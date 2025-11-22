<?php

namespace App\Services\Orders;

use App\Contracts\Roles\HasGuardUser;
use App\Enums\Orders\OrderStatusEnum;
use App\Models\Orders\Order;
use App\Models\Orders\OrderStatusHistory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class OrderStatusService
{

    private function checkCreateStatus(Order $order): Order
    {
        if ($order->payment->order_price === null) {
            return $order;
        }

        if ($order->payment->paid_at !== null) {
            $order->status = OrderStatusEnum::PAID;
        } else {
            $order->status = OrderStatusEnum::PENDING_PAID;
        }

        $order->save();

        return $order;
    }

    private function checkPendingPaidStatus(Order $order): Order
    {
        if ($order->payment->order_price !== null && $order->payment->paid_at === null) {
            return $order;
        }

        if ($order->payment->paid_at !== null) {
            $order->status = OrderStatusEnum::PAID;
        } else {
            $order->status = OrderStatusEnum::CREATED;
        }

        $order->save();

        return $order;
    }

    private function checkPaidStatus(Order $order): Order
    {
        if ($order->payment->order_price !== null && $order->payment->paid_at !== null && $order->payment->refund_at === null) {
            return $order;
        }

        if ($order->payment->order_price === null) {
            $order->status = OrderStatusEnum::CREATED;
        } else {
            $order->payment->refund_at = null;
            $order->payment->save();
            $order->checkouts()
                ->delete();

            $order->status = OrderStatusEnum::PENDING_PAID;
        }

        $order->save();

        return $order;
    }

    private function checkShippedStatus(Order $order): Order
    {
        if ($order->payment->order_price !== null && $order->payment->paid_at !== null && $order->shipping->trk_number !== null) {
            return $order;
        }

        if ($order->payment->order_price === null) {
            $order->status = OrderStatusEnum::CREATED;
        } elseif ($order->payment->paid_at === null) {
            $order->status = OrderStatusEnum::PENDING_PAID;
        } else {
            $order->status = OrderStatusEnum::PAID;
        }

        $order->save();

        return $order;
    }

    public function autoChangeStatus(Order $order): Order
    {
        if ($order->payment->order_price_with_discount === 0.0 && $order->payment->paid_at === null) {
            $order->payment->paid_at = time();
            $order->payment->save();
        }

        return match ($order->status->value) {
            OrderStatusEnum::CREATED => $this->checkCreateStatus($order),
            OrderStatusEnum::PENDING_PAID => $this->checkPendingPaidStatus($order),
            OrderStatusEnum::PAID => $this->checkPaidStatus($order),
            OrderStatusEnum::SHIPPED => $this->checkShippedStatus($order),
            default => $order
        };
    }

    public function saveHistory(Order $order, HasGuardUser $user): void
    {
        $order->statusHistory()
            ->create(
                [
                    'status' => $order->status,
                    'changer_type' => $user->getMorphType(),
                    'changer_id' => $user->getId(),
                ]
            );
    }

    public function getHistory(string $orderId): Collection
    {
        return OrderStatusHistory::whereOrderId($orderId)
            ->orderBy('created_at')
            ->get();
    }

}
