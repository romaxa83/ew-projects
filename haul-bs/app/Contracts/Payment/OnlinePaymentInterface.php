<?php

namespace App\Contracts\Payment;

use App\Models\Orders\Parts\Order;
use Illuminate\Http\Request;

interface OnlinePaymentInterface
{
    /**
     * Generate link to payment system.
     *
     * @param  Order  $order
     * @param  string  $resultUrl  - Url where the buyer would be redirected.
     * @param  string  $serverUrl
     * @return string|null
     */
    public function redirectUrl(Order $order, string $resultUrl, string $serverUrl): ?string;

    /**
     * Handle server request from payment system.
     *
     * @param  Request  $request
     * @return bool - is successfully paid
     */
    public function handleServerRequest(Request $request): bool;
}
