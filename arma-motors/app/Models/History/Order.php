<?php

namespace App\Models\History;

use App\Helpers\DateTime;
use App\Models\BaseModel;
use App\Models\Media\File;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property int $row_id
 * @property string|null $aa_id
 * @property int|null $sys_id
 * @property string|null $amount_in_words
 * @property float|null $amount_including_vat
 * @property float|null $amount_without_vat
 * @property float|null $amount_vat
 * @property string|null $body_number
 * @property Carbon|null $closing_date
 * @property string|null $current_account
 * @property string|null $date
 * @property Carbon|null $date_of_sale
 * @property string|null $dealer
 * @property string|null $disassembled_parts
 * @property float|null $discount
 * @property float|null $discount_jobs
 * @property float|null $discount_parts
 * @property float|null $jobs_amount_including_vat
 * @property float|null $jobs_amount_vat
 * @property float|null $jobs_amount_without_vat
 * @property string|null $model
 * @property string|null $number
 * @property float|null $parts_amount_including_vat
 * @property float|null $parts_amount_vat
 * @property float|null $parts_amount_without_vat
 * @property string|null $producer
 * @property string|null $recommendations
 * @property string|null $repair_type
 * @property string|null $state_number
 * @property float|null $mileage
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read OrderPart[]|Collection $parts
 * @property-read OrderJob[]|Collection $jobs
 * @property-read OrderCustomer|null $customer
 * @property-read OrderDispatcher|null $dispatcher
 * @property-read OrderOrganization|null $organization
 * @property-read OrderOwner|null $owner
 * @property-read OrderPayer|null $payer
 */
class Order extends BaseModel
{
    use HasFactory;

    public const TABLE = 'history_orders';
    protected $table = self::TABLE;

    protected $dates = [
        'closing_date',
        'date_of_sale',
    ];

    public function parts(): HasMany
    {
        return $this->hasMany(OrderPart::class, 'row_id', 'id');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(OrderJob::class, 'row_id', 'id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(OrderCustomer::class, 'id', 'row_id');
    }

    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(OrderDispatcher::class, 'id', 'row_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(OrderOrganization::class, 'id', 'row_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(OrderOwner::class, 'id', 'row_id');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(OrderPayer::class, 'id', 'row_id');
    }

    public function file(): MorphOne
    {
        return $this->morphOne(File::class, 'entity');
    }

    public function carItem(): HasOne
    {
        return $this->hasOne(CarItem::class, 'id', 'row_id');
    }

    public function scopeUserMobile(Builder $query)
    {
        if($user = \Auth::user()){
            return $query->whereHas('carItem', function ($q) use ($user) {
                return $q->whereHas('car', function($q) use ($user) {
                    $q->where('user_id' , $user->id);
                });
            });
        }

        return $query;
    }

    public function scopeCarID(Builder $query, $id)
    {
        return $query->whereHas('carItem', function ($q) use ($id) {
            return $q->whereHas('car', function($q) use ($id) {
                $q->where('id' , $id);
            });
        });
    }

    public function scopeFromClosed(Builder $query, $from)
    {
        return $query->where('closing_date','>', DateTime::fromMillisecondToDate($from));
    }

    public function scopeToClosed(Builder $query, $to)
    {
        return $query->where('closing_date','<', DateTime::fromMillisecondToDate($to));
    }

    public function fileUploadDir(): string
    {
        return "files/order-history/{$this->id}";
    }

    public function fileName(string $type, string $ext = 'pdf'): string
    {
        return "{$type}_{$this->aa_id}.{$ext}";
    }

    public function storagePath(string $type, string $ext = 'pdf'): string
    {
        return "{$this->fileUploadDir()}/{$this->fileName($type, $ext)}";
    }
}

