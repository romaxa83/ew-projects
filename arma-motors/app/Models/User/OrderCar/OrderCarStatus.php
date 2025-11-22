<?php

namespace App\Models\User\OrderCar;

use App\Models\BaseModel;
use App\Models\Order\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * авто в заказе
 * @property int $id
 * @property int $order_car_id
 * @property int $status_id
 * @property int $status
 * @property Carbon|null $date_at
 */

class OrderCarStatus extends BaseModel
{
    const STATUS_DONE = 'done';
    const STATUS_SKIP = 'skip';
    const STATUS_CURRENT = 'current';
    const STATUS_WAIT = 'wait';

    public $timestamps = false;

    public const TABLE_NAME = 'user_car_order_statuses';
    protected $table = self::TABLE_NAME;

    protected $dates = [
        'date_at',
    ];

    public static function stateList(): array
    {
        return [
            self::STATUS_WAIT => __('translation.order.car.state.wait'),
            self::STATUS_CURRENT => __('translation.order.car.state.current'),
            self::STATUS_DONE => __('translation.order.car.state.done'),
            self::STATUS_SKIP => __('translation.order.car.state.skip')
        ];
    }

    public function statusName(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class,'status_id', 'id');
    }

    public function carOrder(): BelongsTo
    {
        return $this->belongsTo(OrderCar::class,'order_car_id', 'id');
    }
}
