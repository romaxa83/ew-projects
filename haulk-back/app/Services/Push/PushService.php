<?php

namespace App\Services\Push;

use App\Models\Orders\Order;
use App\Models\PushNotifications\PushNotificationTask;
use App\Models\Users\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Lang;

class PushService
{
    private function has24HoursBefore(int $timestamp): bool
    {
        return $timestamp - now()->timestamp > PushNotificationTask::SECONDS_IN_DAY;
    }

    public function onOrderSoftDelete(int $order_id): void
    {
        PushNotificationTask::where('order_id', $order_id)
            ->update(
                [
                    'is_sent' => true,
                ]
            );
    }

    public function onOrderForceDelete(int $order_id): void
    {
        PushNotificationTask::where('order_id', $order_id)
            ->delete();
    }

    /**
     * @param Order $order
     */
    public function onOrderRestore(Order $order): void
    {
        PushNotificationTask::where([
            'type' => PushNotificationTask::DELETE_ORDER,
            'order_id' => $order->id
        ])->delete();
    }

    public function onOrderStatusNew(Order $order): void
    {
        if (isset($order->pickup_time['from'])) {
            $pickup_date = Carbon::createFromTimestamp(
                $order->pickup_date,
                $order->pickup_contact['timezone']
            )
            ->setTimeFromTimeString($order->pickup_time['from'])
            ->timestamp;
        } else {
            $pickup_date = $order->pickup_date;
        }

        if ($order->pickup_date && $order->dispatcher) {
            // dispatcher_pickup_24_once
            if ($this->has24HoursBefore($pickup_date)) {
                $this->pushDispatcher(
                    $order,
                    PushNotificationTask::DISPATCHER_PICKUP_24_ONCE,
                    $pickup_date - PushNotificationTask::SECONDS_IN_DAY
                );
            }

            // dispatcher_pickup_1_once
            $this->pushDispatcher(
                $order,
                PushNotificationTask::DISPATCHER_PICKUP_1_ONCE,
                $pickup_date - PushNotificationTask::SECONDS_IN_HOUR
            );
        }

        if ($order->driver) {
            // driver_new_order_once
            $this->pushDriver(
                $order,
                PushNotificationTask::DRIVER_NEW_ORDER_ONCE
            );

            if ($order->pickup_date) {
                // driver_pickup_24_once
                if ($this->has24HoursBefore($pickup_date)) {
                    $this->pushDriver(
                        $order,
                        PushNotificationTask::DRIVER_PICKUP_24_ONCE,
                        $pickup_date - PushNotificationTask::SECONDS_IN_DAY
                    );
                }

                // driver_pickup_1_once
                $this->pushDriver(
                    $order,
                    PushNotificationTask::DRIVER_PICKUP_1_ONCE,
                    $pickup_date - PushNotificationTask::SECONDS_IN_HOUR
                );
            }
        }
    }

    public function onOrderStatusPickedUp(Order $order): void
    {
        if (!$order->delivery_date) {
            return;
        }

        if (isset($order->delivery_time['from'])) {
            $delivery_date = Carbon::createFromTimestamp(
                $order->delivery_date,
                $order->delivery_contact['timezone']
            )
            ->setTimeFromTimeString($order->delivery_time['from'])
            ->timestamp;
        } else {
            $delivery_date = $order->delivery_date;
        }

        if ($order->dispatcher) {
            // dispatcher_delivery_24_once
            if ($this->has24HoursBefore($delivery_date)) {
                $this->pushDispatcher(
                    $order,
                    PushNotificationTask::DISPATCHER_DELIVERY_24_ONCE,
                    $delivery_date - PushNotificationTask::SECONDS_IN_DAY
                );
            }

            // dispatcher_delivery_1_once
            $this->pushDispatcher(
                $order,
                PushNotificationTask::DISPATCHER_DELIVERY_1_ONCE,
                $delivery_date - PushNotificationTask::SECONDS_IN_HOUR
            );
        }

        if ($order->driver) {
            // driver_delivery_24_once
            if ($this->has24HoursBefore($delivery_date)) {
                $this->pushDriver(
                    $order,
                    PushNotificationTask::DRIVER_DELIVERY_24_ONCE,
                    $delivery_date - PushNotificationTask::SECONDS_IN_DAY
                );
            }

            // driver_delivery_1_once
            $this->pushDriver(
                $order,
                PushNotificationTask::DRIVER_DELIVERY_1_ONCE,
                $delivery_date - PushNotificationTask::SECONDS_IN_HOUR
            );
        }
    }

    public function onReassignDriverOrders(Collection $orders): void
    {
        /**@var Order $first*/
        $first = $orders->first();

        $first->refresh();

        if ($orders->count() === 1) {
            $this->pushDriver($first, PushNotificationTask::DRIVER_ORDER_REASSIGN);
            return;
        }

        $this->pushUserWithoutOrder(
            $first->driver,
            PushNotificationTask::DRIVER_ORDERS_REASSIGN,
            ['order_count' => $orders->count()]
        );
    }

    public function onReassignDispatcherOrders(Collection $orders): void
    {
        /**@var Order $first*/
        $first = $orders->first();

        $first->refresh();

        if ($orders->count() === 1) {
            $this->pushDispatcher($first, PushNotificationTask::DISPATCHER_ORDER_REASSIGN);
            return;
        }

        $this->pushUserWithoutOrder(
            $first->dispatcher,
            PushNotificationTask::DISPATCHER_ORDERS_REASSIGN,
            ['order_count' => $orders->count()]
        );
    }

    public function onReassignDispatcherDrivers(User $dispatcher, Collection $drivers): void
    {
        $this->pushUserWithoutOrder(
            $dispatcher,
            PushNotificationTask::DISPATCHER_DRIVERS_REASSIGN,
            ['drivers_count' => $drivers->count()]
        );

        $drivers->map(
            function (User $item) {
                $this->pushUserWithoutOrder($item,PushNotificationTask::DRIVER_REASSIGN_DISPATCHER);
            }
        );
    }

    public function onDeleteOrder(Order $order): void
    {
        if ($order->driver === null) {
            return;
        }

        $this->pushUserWithoutOrder(
            $order->driver,
            PushNotificationTask::DELETE_ORDER,
            ['load_id' => $order->load_id]
        );
    }

    public function pushUser(Order $order, User $user, string $type, $when = null, $is_hourly = false): void
    {
        $task = PushNotificationTask::where([
            'type' => $type,
            'order_id' => $order->id,
            'user_id' => $user->id,
        ])->first();

        if (!$task) {
            PushNotificationTask::create([
                'type' => $type,
                'order_id' => $order->id,
                'user_id' => $user->id,
                'when' => $when ?? now()->timestamp,
                'message' => Lang::get('push.' . $type, ['load_id' => $order->load_id], $user->language ?? 'en'),
                'is_hourly' => $is_hourly,
            ]);
        } elseif (!$task->is_sent) {
            if (!$task->is_hourly) {
                $task->when = $when ?? now()->timestamp;
            }

            $task->message = Lang::get('push.' . $type, ['load_id' => $order->load_id], $user->language ?? 'en');
            $task->save();
        }
    }

    public function pushUserWithoutOrder(User $user, string $type, array $noteData = [], $when = null, $is_hourly = false): void
    {
        PushNotificationTask::create([
            'type' => $type,
            'order_id' => null,
            'user_id' => $user->id,
            'when' => $when ?? now()->timestamp,
            'message' => Lang::get('push.' . $type, $noteData, $user->language ?? 'en'),
            'is_hourly' => $is_hourly,
        ]);
    }

    public function pushDispatcher(Order $order, string $type, $when = null, $is_hourly = false): void
    {
        $this->pushUser($order, $order->dispatcher, $type, $when, $is_hourly);
    }

    public function pushDriver(Order $order, string $type, $when = null, $is_hourly = false): void
    {
        $this->pushUser($order, $order->driver, $type, $when, $is_hourly);
    }

    public function deleteDriverPush(Order $order, string $type): void
    {
        PushNotificationTask::where([
            ['type', $type],
            ['order_id', $order->id],
            ['user_id', $order->driver->id]
        ])->delete();
    }

    public function deleteNotification(Order $order, User $user, string $type): void
    {
        PushNotificationTask::where([
            ['type', $type],
            ['order_id', $order->id],
            ['user_id', $user->id],
            ['is_sent', false],
        ])->delete();
    }

    public function deleteUserNotifications(Order $order, User $user): void
    {
        PushNotificationTask::where([
            ['order_id', $order->id],
            ['user_id', $user->id],
            ['is_sent', false],
        ])->delete();
    }

    public function deleteDispatcherNotifications(Order $order): void
    {
        PushNotificationTask::where([
            ['order_id', $order->id],
            ['type', 'like', 'dispatcher%'],
            ['is_sent', false],
        ])->delete();
    }

    public function deleteDriverNotifications(Order $order): void
    {
        PushNotificationTask::where([
            ['order_id', $order->id],
            ['type', 'like', 'driver%'],
            ['is_sent', false],
        ])->delete();
    }

    public function markAsSent(Order $order, User $user, string $type): void
    {
        PushNotificationTask::where([
            ['type', $type],
            ['order_id', $order->id],
            ['user_id', $user->id],
        ])->update([
            'is_sent' => true,
        ]);
    }
}
