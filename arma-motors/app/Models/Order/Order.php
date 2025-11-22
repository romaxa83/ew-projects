<?php

namespace App\Models\Order;

use App\Casts\UuidCast;
use App\Helpers\DateTime;
use App\Models\Admin\Admin;
use App\Models\Agreement\Agreement;
use App\Models\BaseModel;
use App\Models\Catalogs\Service\Service;
use App\Models\Media\File;
use App\Models\User\User;
use App\Types\Order\PaymentStatus;
use App\Types\Order\Status;
use App\Types\Permissions;
use App\ValueObjects\Phone;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string|null $uuid
 * @property int $service_id
 * @property int $user_id
 * @property int|null $admin_id
 * @property string $communication
 * @property int $status
 * @property int $payment_status
 * @property int|null $agreement_id
 * @property Carbon|null $closed_at
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property boolean $send_push_process
 * @property boolean $send_push_close
 *
 * @property-read User|null user
 * @property-read Admin|null admin
 * @property-read Service|null service
 * @property-read Additions|null additions
 * @property-read File|null files
 * @property-read File|null billFile
 * @property-read File|null actFile
 * @property-read Agreement|Collection agreements
 * @property-read Agreement|Collection agreementsAccept
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Order forCurrent()
 */
class Order extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    // тип для файла (страховка)
    public const FILE_ACT_TYPE  = 'act';
    public const FILE_BILL_TYPE = 'bill';

    // период по времени
    public const PERIOD_TODAY    = 'today';
    public const PERIOD_INCOMING = 'incoming';

    // тип заявок
    public const TYPE_ORDINARY  = 'ordinary';   //обычная заявка
    public const TYPE_RECOMMEND = 'recommend';  //заявка создана из рекомендаций
    public const TYPE_AGREEMENT = 'agreement';  //заявка создана из согласования

    protected $table = self::TABLE_NAME;
    public const TABLE_NAME = 'orders';

    protected $casts = [
        'uuid' => UuidCast::class,
        'send_push_process' => 'boolean',
        'send_push_close' => 'boolean',
    ];

    protected $fillable = [
        'send_push_process',
        'send_push_close'
    ];

    protected $dates = [
        'closed_at',
    ];

    // сервисы которые обслуживаются сервисом АА
    public function isRelateToAA(): bool
    {
        return $this->service->isRelateToAA();
    }

    // сервисы которые обслуживаются данной системой
    public function isRelateToSystem(): bool
    {
        return $this->service->isRelateToSystem();
    }

    public function isClose(): bool
    {
        return Status::isCloseStatus($this->status);
    }

    public function isCreated(): bool
    {
        return Status::isCreateStatus($this->status);
    }

    public function isProcess(): bool
    {
        return Status::isProcessStatus($this->status);
    }

    public function isDraft(): bool
    {
        return Status::isDraftStatus($this->status);
    }

    public function isReject(): bool
    {
        return Status::isRejectStatus($this->status);
    }

    // relation

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function additions(): HasOne
    {
        return $this->HasOne(Additions::class);
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'entity');
    }

    public function billFile(): MorphOne
    {
        return $this->morphOne(File::class, 'entity')
            ->where('type', self::FILE_BILL_TYPE);
    }

    public function actFile(): MorphOne
    {
        return $this->morphOne(File::class, 'entity')
            ->where('type', self::FILE_ACT_TYPE);
    }

    public function agreements(): HasMany
    {
        return $this->HasMany(Agreement::class, 'base_order_uuid', 'uuid');
    }

    public function agreementsAccept(): HasMany
    {
        return $this->HasMany(Agreement::class, 'base_order_uuid', 'uuid')
            ->where('status', Agreement::STATUS_VERIFY);
    }

    public function getFileByType($type): null|File
    {
        if($type === self::FILE_BILL_TYPE){
            return $this->billFile;
        }

        if($type === self::FILE_ACT_TYPE){
            return $this->actFile;
        }

        return null;
    }

    public function getStateAttribute()
    {
        return Status::create($this->status);
    }

    public function getPaymentStateAttribute()
    {
        return PaymentStatus::create($this->payment_status);
    }

    public function getRelatedSystemAttribute()
    {
        return $this->isRelateToSystem();
    }

    public function scopeOrderGate(EloquentBuilder $query): EloquentBuilder
    {
        /** @var $user Admin */
        $user = \Auth::guard(Admin::GUARD)->user();
        if($user->isSuperAdmin() || $user->hasPermissionTo(Permissions::ORDER_CAN_SEE)){
            return $query;
        }

        return $query
            ->where('admin_id', $user->id)
            ->orWhere("service_id", $user->service_id)
            ;
    }

    public function scopeUserName(EloquentBuilder $query, string $search)
    {
        return $query->with('user')
            ->whereHas('user', fn ($q) => $q->where('name', 'like', '%' . $search . '%'));
    }

    public function scopeResponsiveName(EloquentBuilder $query, string $name)
    {
        return $query->with(['admin', 'additions'])
            ->where(fn($q) =>
                $q->orWhereHas('admin', fn ($q) => $q->where('name', 'like', '%' . $name . '%'))
                    ->orWhereHas('additions', fn($q) => $q->where('responsible', 'like', '%' . $name . '%'))
            );
    }

    public function scopeBrandId(EloquentBuilder $query, $brandId)
    {
        return $query->with('additions')
            ->whereHas('additions', fn ($q) => $q->where('brand_id', $brandId));
    }

    public function scopeDealershipId(EloquentBuilder $query, $dealershipId)
    {
        return $query->with('additions')
            ->whereHas('additions', fn ($q) => $q->where('dealership_id', $dealershipId));
    }

    public function scopeUserPhoneSearch(EloquentBuilder $query, string $search)
    {
        $phone = new Phone($search);
        return $query->with('user')
            ->whereHas('user', fn ($q) => $q->where('phone', $phone));
    }

    public function scopeModelId(EloquentBuilder $query, $modelId)
    {
        return $query->with('additions')
            ->whereHas('additions', fn ($q) => $q->where('model_id', $modelId));
    }

    public function scopeForCurrent(EloquentBuilder $query)
    {
        return $query->whereIn('status', Status::statusForCurrent());
    }

    public function scopeForHistory(EloquentBuilder $query)
    {
        return $query->whereIn('status', Status::statusForHistory());
    }

    public function scopePeriod(EloquentBuilder $query, $period)
    {
        $today = CarbonImmutable::today();
        $from = CarbonImmutable::now();
        $to = $today->addDay();

        if($period == self::PERIOD_TODAY){
            return $query->with('additions')
                ->whereHas('additions', fn($q) => $q->whereBetween('for_current_filter_date', [$from, $to]));
        }
        if($period == self::PERIOD_INCOMING){
            return $query->with('additions')
                ->whereHas('additions', fn($q) => $q->where('for_current_filter_date', '>', $to));
        }

        return $query;
    }

    public function scopeFromClosed(EloquentBuilder $query, $from)
    {
        return $query->where('closed_at','>', DateTime::fromMillisecondToDate($from));
    }

    public function scopeToClosed(EloquentBuilder $query, $to)
    {
        return $query->where('closed_at','<', DateTime::fromMillisecondToDate($to));
    }

    public function scopePeriodFrom(EloquentBuilder $query, $from)
    {
        $from = DateTime::fromMillisecondToSeconds($from);
        $date = Carbon::createFromTimestamp($from)->format('Y-m-d H:i:s');
        return $query->where('created_at','>', $date);
    }

    public function scopePeriodTo(EloquentBuilder $query, $to)
    {
        $to = DateTime::fromMillisecondToSeconds($to);
        $date = Carbon::createFromTimestamp($to)->format('Y-m-d H:i:s');
        return $query->where('created_at','<', $date);
    }

    public function scopeDealershipOrderBy(EloquentBuilder $query, $type)
    {
        return $query
            ->select('orders.*')
            ->join('order_additions as oad', 'oad.order_id', '=', 'orders.id')
            ->orderBy('oad.dealership_id', $type);
    }

    public function scopeOrderByOnDateAndReal(EloquentBuilder $query, $type)
    {
        return $query
            ->select('orders.*')
            ->join('order_additions as oad', 'oad.order_id', '=', 'orders.id')
            ->orderBy('oad.for_current_filter_date', $type);
    }

    public function scopeCarOrderBy(EloquentBuilder $query, $type)
    {
        return $query
            ->select('orders.*')
            ->join('order_additions as oac', 'oac.order_id', '=', 'orders.id')
            ->orderBy('oac.car_id', $type);
    }

    // for pdf file

    public function fileUploadDir(): string
    {
        return "files/order/{$this->id}";
    }

    public function fileName(string $type, string $ext = 'pdf'): string
    {
        return "{$type}_{$this->uuid}.{$ext}";
    }

    public function storagePath(string $type, string $ext = 'pdf'): string
    {
        return "{$this->fileUploadDir()}/{$this->fileName($type, $ext)}";
    }
}

