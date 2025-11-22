<?php

namespace App\Listeners\Orders;

use App\Events\ModelChanged;
use App\Events\Orders\OrderUpdateEvent;
use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Models\Users\User;
use Auth;

class OrderUpdateListener
{

    public function handle(OrderUpdateEvent $event)
    {
        /**@var Order $order*/
        $order = $event->getOrder();

        /**@var User $editor*/
        $editor = Auth::user();

        if ($order->hasDriver() && $order->wasChanged('driver_id')) {
            $driver = $order->driver;

            event(
                new ModelChanged(
                    $order,
                    'history.driver_assigned_order_by',
                    [
                        'full_name' => $driver->full_name,
                        'load_id' => $order->load_id,
                        'user_id' => $driver->id,
                        'editor_name' => $editor->full_name,
                        'editor_id' => $editor->id,
                    ]
                )
            );
        }

        if (!$order->hasDriver() && $order->wasChanged('driver_id')) {
            event(
                new ModelChanged(
                    $order,
                    'history.driver_removed_order_by',
                    [
                        'load_id' => $order->load_id,
                        'editor_name' => $editor->full_name,
                        'editor_id' => $editor->id,
                    ]
                )
            );
        }

        if ($order->hasDispatcher() && $order->wasChanged('dispatcher_id')) {
            $dispatcher = $order->dispatcher;

            event(
                new ModelChanged(
                    $order,
                    'history.dispatcher_assigned_order_by',
                    [
                        'full_name' => $dispatcher->full_name,
                        'load_id' => $order->load_id,
                        'user_id' => $dispatcher->id,
                        'editor_name' => $editor->full_name,
                        'editor_id' => $editor->id,
                    ]
                )
            );
        }

        if (!$order->hasDispatcher() && $order->wasChanged('dispatcher_id')) {
            event(
                new ModelChanged(
                    $order,
                    'history.dispatcher_removed_order_by',
                    [
                        'load_id' => $order->load_id,
                        'editor_name' => $editor->full_name,
                        'editor_id' => $editor->id,
                    ]
                )
            );
        }

        if (!$order->payment) {
            return;
        }

        $this->checkAddPayment($order->payment, $order, $editor);
    }

    private function checkAddPayment(Payment $payment, Order $order, User $editor): void
    {
        if (!$payment->wasChanged('driver_payment_data_sent') || $payment->driver_payment_data_sent !== true) {
            return;
        }

        if ($payment->wasChanged('driver_payment_comment') && !empty($payment->driver_payment_comment)) {
            event(
                new ModelChanged(
                    $order,
                    'history.driver_not_received_payment',
                    [
                        'full_name' => $editor->full_name,
                        'user_id' => $editor->id,
                        'comment' => $payment->driver_payment_comment,
                        'type' => $payment->customer_payment_location,
                    ]
                )
            );
            return;
        }

        event(
            new ModelChanged(
                $order,
                'history.driver_received_payment',
                [
                    'full_name' => $editor->full_name,
                    'user_id' => $editor->id,
                    'type' => $payment->customer_payment_location,
                ]
            )
        );
    }
}
