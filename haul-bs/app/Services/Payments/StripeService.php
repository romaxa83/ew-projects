<?php

namespace App\Services\Payments;

use App\Models\Orders\Parts\Order;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use Stripe\Stripe;
use Stripe\WebhookSignature;
use Throwable;

class StripeService
{
    protected const CURRENCY = 'usd';
    protected const PAYMENT_METHOD_CARD = 'card';
    protected const PAYMENT_MODE = 'payment';

    public function __construct()
    {
        Stripe::setApiKey($this->getApiKey());
    }

    /**
     * @throws ApiErrorException
     */
    public function createCheckoutSession(Order $order): Session
    {
        $data = [
            'payment_method_types' => [
                static::PAYMENT_METHOD_CARD
            ],
            'mode' => static::PAYMENT_MODE,
            'client_reference_id' => $order->id,
            'success_url' => route('site.thanks-page', ['order_id' => $order->order_number]),
            'cancel_url' => route('site.thanks-page', ['order_id' => $order->order_number]),
        ];

        $itemsResult = [];

        $itemsResult[] = [
            'price' => Price::create([
                'currency' => static::CURRENCY,
                'unit_amount' => ($order->getAmount()) * 100,
                'product_data' => ['name' => 'Order №' . $order->order_number],
            ])->id,
            'quantity' => 1,
        ];


        $data['line_items'] = $itemsResult;

        return Session::create($data);
    }

    /**
     * @throws ApiErrorException
     */
    public function getCheckoutUrl(Order $order): ?string
    {
        $checkoutSession = $this->createCheckoutSession($order);

        return $checkoutSession->url;
    }

    protected function getApiKey(): ?string
    {
        return config('cashier.secret');
    }

    /**
     * @throws Throwable
     */
    public function webhooksVerifications(Request $request): bool
    {
        return WebhookSignature::verifyHeader(
            $request->getContent(),
            $request->header('Stripe-Signature'),
            config('cashier.webhook.secret'),
            config('cashier.webhook.tolerance')
        );
    }
}
