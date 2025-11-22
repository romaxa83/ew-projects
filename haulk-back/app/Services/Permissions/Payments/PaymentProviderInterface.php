<?php


namespace App\Services\Permissions\Payments;


use App\Dto\Payments\PaymentDataAbstract;
use App\Dto\Payments\PaymentMethodRequestDto;

interface PaymentProviderInterface
{
    public const TRANSACTION_APPROVED = 1;
    public const TRANSACTION_DECLINED = 2;

    public function getProviderName(): string;

    public function storePaymentData(PaymentMethodRequestDto $dto): PaymentDataAbstract;

    //public function getPaymentData(string $id): ?PaymentDataAbstract;

    public function deleteByStoredPaymentData(PaymentDataAbstract $paymentData): bool;

    public function deleteByUserData(...$userData): bool;

    public function makePayment(PaymentDataAbstract $paymentData, float $amount): string;

    public function getTransactionStatus(string $transID): ?int;
}
