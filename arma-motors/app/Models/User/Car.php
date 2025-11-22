<?php

namespace App\Models\User;

use App\Casts\CarNumberCast;
use App\Casts\CarVinCast;
use App\Casts\UuidCast;
use App\Models\Admin\Admin;
use App\Models\BaseModel;
use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\Model;
use App\Models\History\CarItem;
use App\Models\Media\File;
use App\Models\Recommendation\Recommendation;
use App\Models\User\Loyalty\Loyalty;
use App\Models\User\OrderCar\OrderCar;
use App\Traits\Media\ImageRelation;
use App\ValueObjects\CarNumber;
use App\ValueObjects\CarVin;
use App\ValueObjects\Phone;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Traits\Tappable;

/**
 * @property int $id
 * @property string|null $uuid
 * @property int $brand_id
 * @property int $model_id
 * @property int $user_id
 * @property string|null $number
 * @property string|null $vin
 * @property string $year
 * @property string $year_deal              // год продажи авто (для программы лояльности)
 * @property int $inner_status              // внутренний статус
 * @property bool $is_verify                // верифицирован в 1с
 * @property bool $is_personal              // персональное авто
 * @property bool $is_buy                   // куплен в арме
 * @property bool $is_add_to_app            // добавлено через мобильное приложение
 * @property bool $selected                 // выбрано как активное
 * @property bool $has_insurance            // имеет ли страховку
 * @property string|null $delete_reason     // причина удаления авто
 * @property string|null $delete_comment    // комментарий к удалению авто
 * @property null|Carbon $deleted_at
 * @property null|Carbon $created_at
 * @property null|Carbon $updated_at
 * @property bool $is_order                 // является ли авто в заказе
 * @property bool $in_garage                // выбрано в гараж пользователя
 * @property bool $owner_uuid               // uuid владельца авто
 * @property bool $aa_status
 * @property string|null $name_aa           // название авто присланное системой AA
 */

class Car extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use ImageRelation;

    // inner status
    public const DRAFT     = 0;
    public const MODERATE  = 1;
    public const VERIFY    = 2;

    // AA status
    public const NONE         = 0;  // нет статуса
    public const ORDER_CAR    = 1;  // авто в заказе
    public const ORDINARY_CAR = 2;  // обычное авто

    // причины удаления
    public const REASON_SOLD   = 'sold';
    public const REASON_OTHER  = 'other';

    // тип для файла (страховка)
    public const FILE_INSURANCE_TYPE = 'insurance';

    public const TABLE_NAME = 'user_cars';

    protected $table = self::TABLE_NAME;

    protected $fillable = [
        'uuid',
    ];

    protected $dates = [
        'year' => 'datetime:Y',
        'year_deal' => 'datetime:Y',
    ];

    protected $casts = [
        'is_verify' => 'boolean',
        'is_personal' => 'boolean',
        'is_buy' => 'boolean',
        'is_add_to_app' => 'boolean',
        'selected' => 'boolean',
        'has_insurance' => 'boolean',
        'is_order' => 'boolean',
        'in_garage' => 'boolean',
        'is_moderate' => 'boolean',
        'uuid' => UuidCast::class,
        'owner_uuid' => UuidCast::class,
        'number' => CarNumberCast::class,
        'vin' => CarVinCast::class,
    ];

    public static function statusList()
    {
        return [
            self::DRAFT => __('translation.car.status.draft'),
            self::MODERATE => __('translation.car.status.moderate'),
            self::VERIFY => __('translation.car.status.verify')
        ];
    }

    public static function deleteReasonList()
    {
        return [
            self::REASON_SOLD => __('translation.car.reason.sold'),
            self::REASON_OTHER => __('translation.car.reason.other')
        ];
    }

    public static function assertStatus($status): void
    {
        if(!array_key_exists($status, self::statusList())){
            throw new \InvalidArgumentException(__('error.not valid car status', ['status' => $status]));
        }
    }

    public static function checkStatus($status): bool
    {
        return array_key_exists($status, self::statusList());
    }

    public static function statusVerify($status): bool
    {
        return $status === self::VERIFY;
    }

    public static function statusModerate($status): bool
    {
        return $status === self::MODERATE;
    }

    public function isVerify(): bool
    {
        return $this->is_verify;
    }

    public function isDraft(): bool
    {
        return $this->inner_status == self::DRAFT;
    }

    public function isModeration(): bool
    {
        return $this->inner_status == self::MODERATE;
    }

    public function isSelected(): bool
    {
        return $this->selected;
    }

    public function hasInsurance(): bool
    {
        return $this->has_insurance;
    }

    public function isOrder(): bool
    {
        return $this->is_order;
    }

    public function inGarage(): bool
    {
        return $this->in_garage;
    }

    // relations

    public function history(): HasOne
    {
        return $this->hasOne(CarItem::class, 'car_uuid', 'uuid');
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'entity');
    }

    public function insuranceFile(): MorphOne
    {
        return $this->morphOne(File::class, 'entity')
            ->where('type', self::FILE_INSURANCE_TYPE);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(Model::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recommendations(): HasMany
    {
        return $this->HasMany(Recommendation::class, "car_uuid", "uuid");
    }

    // данные по заказу, если авто в заказе
    public function carOrder(): HasOne
    {
        return $this->hasOne(OrderCar::class);
    }

    public function userWithTrashed()
    {
        return $this->user()->withTrashed();
    }

    public function confidants(): HasMany
    {
        return $this->HasMany(Confidant::class);
    }

    public function loyalties(): BelongsToMany
    {
        return $this->belongsToMany(
            Loyalty::class,
            'user_car_loyalty_pivot',
            'car_id', 'loyalty_id'
        )->where('loyalties.active', true)->withPivot('active');
    }

    // scopes

    public function scopeCarVinSearch(EloquentBuilder $query, string $search)
    {
        $vin = new CarVin($search);
        return $query->where('vin', $vin);
    }

    public function scopeCarNumberSearch(EloquentBuilder $query, string $search)
    {
        $number = new CarNumber($search);
        return $query->where('number', $number);
    }

    public function scopeUserNameSearch(EloquentBuilder $query, string $search)
    {
        return $query->with('user')
            ->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
    }

    public function scopeUserPhoneSearch(EloquentBuilder $query, string $search)
    {
        $phone = new Phone($search);
        return $query->with('user')
            ->whereHas('user', function ($q) use ($phone) {
                $q->where('phone', $phone);
            });
    }

    public function scopeIsOrder(EloquentBuilder $query, bool $isOrder)
    {
        return $query->where('is_order', $isOrder);
    }

    public function scopeCarGate(EloquentBuilder $query): EloquentBuilder
    {
        /** @var $user Admin */
        $user = \Auth::guard(Admin::GUARD)->user();
        if($user->isSuperAdmin()){
            return $query;
        }

        $user->load(['dealership']);
        if(null == $user->dealership){
            return $query;
        }

        return $query
            ->where('brand_id', $user->dealership->brand_id);
    }

    // ACCESSORS

    public function getCarNameAttribute()
    {
        $this->load(['brand', 'model']);

        return "{$this->brand->name} {$this->model->name}";
    }
}

