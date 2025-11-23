<?php

namespace WezomCms\Orders\Contracts;

use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderPaymentInformation;

interface PaymentDriverInterface
{
    public function validatePayment(array $data = []): bool;

    public function handleOrder(Order $order): Order;

    public function handleOrderPayment(Order $order): Order;

    public function getValidationRules(): array;

    public function getPaymentPayload(): array;

    public function renderPaymentData(OrderPaymentInformation $paymentInfo): string;
}
