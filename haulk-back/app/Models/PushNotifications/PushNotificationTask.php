<?php

namespace App\Models\PushNotifications;

use App\Models\Users\User;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin Eloquent
 */
class PushNotificationTask extends Model
{
    public const DISPATCHER_PICKUP_1_ONCE = 'dispatcher_pickup_1_once';
    public const DISPATCHER_PICKUP_24_ONCE = 'dispatcher_pickup_24_once';
    public const DISPATCHER_DELIVERY_1_ONCE = 'dispatcher_delivery_1_once';
    public const DISPATCHER_DELIVERY_24_ONCE = 'dispatcher_delivery_24_once';
    public const DRIVER_PICKUP_1_ONCE = 'driver_pickup_1_once';
    public const DRIVER_PICKUP_24_ONCE = 'driver_pickup_24_once';
    public const DRIVER_DELIVERY_1_ONCE = 'driver_delivery_1_once';
    public const DRIVER_DELIVERY_24_ONCE = 'driver_delivery_24_once';
    public const DISPATCHER_NEED_REVIEW_ONCE = 'dispatcher_need_review_once';
    public const DRIVER_NEW_ORDER_ONCE = 'driver_new_order_once';
    public const DRIVER_ORDER_REASSIGN = 'driver_order_reassign';
    public const DRIVER_ORDERS_REASSIGN = 'driver_orders_reassign';
    public const DISPATCHER_ORDER_REASSIGN = 'dispatcher_order_reassign';
    public const DISPATCHER_ORDERS_REASSIGN = 'dispatcher_orders_reassign';
    public const DISPATCHER_DRIVERS_REASSIGN = 'dispatcher_drivers_reassign';
    public const DRIVER_REASSIGN_DISPATCHER = 'driver_reassign_dispatcher';
    public const DELETE_ORDER = 'delete_order';

    public const RETRY_COUNT = 3;

    public const SECONDS_IN_HOUR = 3600;
    public const SECONDS_IN_DAY = 86400;

    protected $fillable = [
        'type',
        'order_id',
        'user_id',
        'when',
        'message',
        'is_hourly',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function needOrderManagerAlert(): bool
    {
        return in_array(
            $this->type,
            [
                self::DISPATCHER_PICKUP_1_ONCE,
                self::DISPATCHER_PICKUP_24_ONCE,
                self::DISPATCHER_DELIVERY_1_ONCE,
                self::DISPATCHER_DELIVERY_24_ONCE,
            ],
            true
        );
    }

    public function isFirstTry(): bool
    {
        return $this->retry === 0;
    }
}
