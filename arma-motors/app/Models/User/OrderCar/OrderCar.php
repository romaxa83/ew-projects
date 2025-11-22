<?php

namespace App\Models\User\OrderCar;

use App\Casts\MoneyCast;
use App\Models\BaseModel;
use App\Models\User\Car;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * авто в заказе
 * @property int $id
 * @property int $car_id
 * @property int $payment_status
 * @property int $sum_discount
 * @property int $sum
 * @property string $order_number
 * @property string $files
 */

class OrderCar extends BaseModel
{
    use HasFactory;

    public const NONE = 0;

    public $timestamps = false;
    public const TABLE_NAME = 'user_car_orders';

    protected $table = self::TABLE_NAME;

//    protected $casts = [
//        'sum' => MoneyCast::class,
//    ];

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'car_id', 'id');
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(OrderCarStatus::class, 'order_car_id', 'id');
    }
}
