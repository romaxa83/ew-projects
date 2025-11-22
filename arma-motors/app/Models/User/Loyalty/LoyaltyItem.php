<?php

namespace App\Models\User\Loyalty;

use App\Helpers\ConvertNumber;
use App\Models\BaseModel;
use App\Models\User\Car;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $loyalty_id
 * @property int $car_id
 * @property bool $active
 */

class LoyaltyItem extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE_NAME = 'user_car_loyalty_pivot';

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $table = self::TABLE_NAME;

    public function getDiscountFloatAttribute(): float
    {
        return ConvertNumber::fromNumberToFloat($this->discount);
    }

    // reletions

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function loyalty(): BelongsTo
    {
        return $this->belongsTo(Loyalty::class, 'loyalty_id', 'id');
    }

    // @todo нужно подумать как не выводить с удалеными машинами
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'car_id', 'id')->withTrashed();
    }

    // scopes

    public function scopeLoyaltyTypeSearch(EloquentBuilder $query, $type): EloquentBuilder
    {
        return $query->with('loyalty')
            ->whereHas('loyalty', function(EloquentBuilder $q) use ($type) {
                $q->where('type', $type);
            });
    }

    public function scopeBrandSearch(EloquentBuilder $query, $brandId): EloquentBuilder
    {
        return $query->with('loyalty')
            ->whereHas('loyalty', function(EloquentBuilder $q) use ($brandId) {
                $q->where('brand_id', $brandId);
            });
    }

    public function scopeDiscountSearch(EloquentBuilder $query, $discount): EloquentBuilder
    {
        $discount = ConvertNumber::fromFloatToNumber($discount);
        return $query->with('loyalty')
            ->whereHas('loyalty', function(EloquentBuilder $q) use ($discount) {
                $q->where('discount', $discount);
            });
    }

    public function scopeUserNameSearch(EloquentBuilder $query, string $name): EloquentBuilder
    {
        return $query->with('user')
            ->whereHas('user', function(EloquentBuilder $q) use ($name) {
                $q->where('name','like', '%' . $name . '%');
            });
    }
}

