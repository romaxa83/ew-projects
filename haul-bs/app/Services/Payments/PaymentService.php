<?php

namespace App\Services\Payments;

use App\Contracts\Payment\OnlinePaymentInterface;
use App\Models\Orders\Parts\Order;

class PaymentService
{
    public function getPaymentLink(Order $order): ?string
    {
        $paymentDriver = $order->makeDriver();

        if (!$paymentDriver instanceof OnlinePaymentInterface) {
            return null;
        }

        return $paymentDriver->redirectUrl(
            $order,
            route('site.thanks-page', $order->order_number),
            route('site.thanks-page', $order->order_number)
        );
    }
}
