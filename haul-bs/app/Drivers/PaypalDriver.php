<?php

namespace App\Drivers;

use App\Contracts\Payment\ExpectsResponse;
use App\Contracts\Payment\OnlinePaymentInterface;
use App\Contracts\Payment\PaymentDriverInterface;
use App\Dto\Orders\BS\OrderPaymentDto;
use App\Models\Orders\Parts\Order;
use App\Services\Orders\Parts\OrderPaymentService;
use App\Services\Payments\PaypalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class PaypalDriver implements PaymentDriverInterface, OnlinePaymentInterface, ExpectsResponse
{
    public const PAYPAL_DRIVER = 'paypal';

    /**
     * @inheritDoc
     */
    public static function driver(): string
    {
        return static::PAYPAL_DRIVER;
    }

    public function hasBankAccounts(): bool
    {
        return false;
    }

    public function availableForRetail(): bool
    {
        return true;
    }

    /**
     * Generate link to payment system.
     *
     * @param Order $order
     * @param string $resultUrl - Url where the buyer would be redirected.
     * @param string $serverUrl
     * @return string|null
     */
    public function redirectUrl(Order $order, string $resultUrl, string $serverUrl): ?string
    {
        return $this->getPaymentUrl($order);
    }

    /**
     * Handle server request from payment system.
     *
     * @param  Request  $request
     * @return bool - is successfully paid
     * @throws Throwable
     */
    public function handleServerRequest(Request $request): bool
    {
        /** @var PaypalService $paypalService */
        $paypalService = resolve(PaypalService::class);
        return $paypalService->webhooksVerifications($request);
    }

    public function successResponse(Order $order, Request $request): JsonResponse
    {
        if($request->input('resource.status') === 'APPROVED') {
            $dto = OrderPaymentDto::byArgs([
                'amount' => $order->total_amount,
                'payment_date' => now()->format('m/d/Y'),
                'payment_method' => $order->payment_method->value,
            ]);
            app(OrderPaymentService::class)->add($order, $dto);
        }

        return response()->json();
    }

    public function failedResponse(Order $order): JsonResponse
    {
        return response()->json([], 500);
    }

    public function getPaymentUrl(Order $order): ?string
    {
        $paypalService = resolve(PaypalService::class);

        try {
            return $paypalService->createPaymentOrder($order);
        } catch (Throwable $throwable) {
            report($throwable);

            return null;
        }
    }
}
