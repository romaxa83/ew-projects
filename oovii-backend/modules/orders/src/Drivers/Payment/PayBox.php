<?php

namespace WezomCms\Orders\Drivers\Payment;

use WezomCms\Orders\Contracts\PaymentDriverInterface;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderPaymentInformation;

class PayBox implements PaymentDriverInterface
{
    public const KEY = 'pay-box';
    public const PAY_BOX_URL = 'https://my.paybox.money';

    public function validatePayment(array $data = []): bool
    {
        return true;
    }

    public function handleOrder(Order $order): Order
    {
        return $order;
    }

    public function handleOrderPayment(Order $order): Order
    {
        return $order;
    }

    public function getValidationRules(): array
    {
        return [
            [],
            [],
            [],
        ];
    }

    public function getPaymentPayload(): array
    {
        return [
            'payment_driver' => self::KEY,
            'check_url' => route('api.v1.mobile.pay-box.check'),
            'result_url' => route('api.v1.mobile.pay-box.result'),
        ];
    }

    public function renderPaymentData(OrderPaymentInformation $paymentInfo): string
    {
        $paymentData = $paymentInfo->payment_data;

        if (empty($paymentData)) {
            return '';
        }

        $paymentId = data_get($paymentData, 'pg_payment_id');

        return view('cms-orders::admin.drivers.payment.pay-box', [
            'success' => (bool)data_get($paymentData, 'pg_result'),
            'cardOwner' => data_get($paymentData, 'pg_card_owner'),
            'cardNumber' => data_get($paymentData, 'pg_card_pan'),
            'cardBrand' => data_get($paymentData, 'pg_card_brand'),
            'paymentId' => $paymentId,
            'paymentLink' => $this->getPaymentLink($paymentId),
            'error' => data_get($paymentData, 'pg_failure_description'),
        ])->render();
    }

    private function getPaymentLink(string $paymentId): string
    {
        return sprintf(
            '%s/%s/%s',
            self::PAY_BOX_URL,
            'pay',
            $paymentId
        );
    }
}
