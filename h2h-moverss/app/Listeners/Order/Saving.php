<?php

namespace App\Listeners\Order;

use App\Enums\Orders\ActivityType;
use App\Events\OrderUpdated;
use App\Models\Order;

class Saving
{

    /**
     * Логируем изменения в Order.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(OrderUpdated $event)
    {
        /** @var Order */
        $order = $event->order;
        if ((int) $order->user_id !== (int) $order->getOriginal('user_id')) {
            $order->addActivity(ActivityType::User->value, [
                'from' => (int) $order->getOriginal('user_id'),
                'to' => (int) $order->user_id,
            ]);
        }
        if ((int) $order->division_id !== (int) $order->getOriginal('division_id')) {
            $order->addActivity(ActivityType::Division->value, [
                'from' => (int) $order->getOriginal('division_id'),
                'to' => (int) $order->division_id,
            ]);
        }
        if ((int) $order->source_id !== (int) $order->getOriginal('source_id')) {
            $order->addActivity(ActivityType::Source->value, [
                'from' => (int) $order->getOriginal('source_id'),
                'to' => (int) $order->source_id,
            ]);
        }
        if ((int) $order->status_id !== (int) $order->getOriginal('status_id')) {
            $order->addActivity(ActivityType::Status->value, [
                'from' => (int) $order->getOriginal('status_id'),
                'to' => (int) $order->status_id,
            ]);
        }
        if ((int) $order->move_size_id !== (int) $order->getOriginal('move_size_id')) {
            $order->addActivity(ActivityType::Move_size->value, [
                'from' => (int) $order->getOriginal('move_size_id'),
                'to' => (int) $order->move_size_id,
            ]);
        }

        // Sizing
        if ((int) $order->sizing_is_auto !== (int) $order->getOriginal('sizing_is_auto')) {
            $order->addActivity(ActivityType::Sizing_is_auto->value, [
                'from' => (int) $order->getOriginal('sizing_is_auto'),
                'to' => (int) $order->sizing_is_auto,
            ]);
        }
        if ((float) $order->sizing_volume !== (float) $order->getOriginal('sizing_volume')) {
            $order->addActivity(ActivityType::Sizing_volume->value, [
                'from' => (float) $order->getOriginal('sizing_volume'),
                'to' => (float) $order->sizing_volume,
            ]);
        }
        if ((float) $order->sizing_weight !== (float) $order->getOriginal('sizing_weight')) {
            $order->addActivity(ActivityType::Sizing_weight->value, [
                'from' => (float) $order->getOriginal('sizing_weight'),
                'to' => (float) $order->sizing_weight,
            ]);
        }
        if ((int) $order->type !== (int) $order->getOriginal('type')) {
            $order->addActivity('type', [
                'from' => (int) $order->getOriginal('type'),
                'to' => (int) $order->type,
            ]);
        }
    }
}
