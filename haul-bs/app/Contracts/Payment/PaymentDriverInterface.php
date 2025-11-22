<?php

namespace App\Contracts\Payment;

interface PaymentDriverInterface
{
    /**
     * Driver name.
     */
    public static function driver(): string;

    public function hasBankAccounts(): bool;

    public function availableForRetail(): bool;
}
