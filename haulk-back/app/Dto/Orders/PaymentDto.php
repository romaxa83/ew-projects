<?php

namespace App\Dto\Orders;

use App\Dto\BaseDto;

/**
 * @property-read string|null $terms
 * @property-read string|null $invoiceNotes
 * @property-read float $totalCarrierAmount
 * @property-read float|null $customerPaymentAmount
 * @property-read int|null $customerPaymentMethodId
 * @property-read string|null $customerPaymentLocation
 * @property-read float|null $brokerPaymentAmount
 * @property-read int|null $brokerPaymentMethodId
 * @property-read int|null $brokerPaymentDays
 * @property-read string|null $brokerPaymentBegins
 * @property-read float|null $brokerFeeAmount
 * @property-read int|null $brokerFeeMethodId
 * @property-read int|null $brokerFeeDays
 * @property-read string|null $brokerFeeBegins
 */
class PaymentDto extends BaseDto
{
    protected ?string $terms;
    protected ?string $invoiceNotes;
    protected float $totalCarrierAmount;
    protected ?float $customerPaymentAmount = null;
    protected ?int $customerPaymentMethodId = null;
    protected ?string $customerPaymentLocation = null;
    protected ?float $brokerPaymentAmount = null;
    protected ?int $brokerPaymentMethodId = null;
    protected ?int $brokerPaymentDays = null;
    protected ?string $brokerPaymentBegins = null;
    protected ?float $brokerFeeAmount = null;
    protected ?int $brokerFeeMethodId = null;
    protected ?int $brokerFeeDays = null;
    protected ?string $brokerFeeBegins = null;

    public static function init(array $args): self
    {
        $dto = new self();

        $dto->terms = $args['terms'] ?? null;
        $dto->invoiceNotes = $args['invoice_notes'] ?? null;
        $dto->totalCarrierAmount = (float)$args['total_carrier_amount'];

        if (array_key_exists('customer_payment_amount', $args) && !empty($args['customer_payment_amount'])) {
            $dto->customerPaymentAmount = (float)$args['customer_payment_amount'];
            $dto->customerPaymentMethodId = (int)$args['customer_payment_method_id'];
            $dto->customerPaymentLocation = $args['customer_payment_location'];
        }

        if (array_key_exists('broker_payment_amount', $args) && !empty($args['broker_payment_amount'])) {
            $dto->brokerPaymentAmount = (float)$args['broker_payment_amount'];
            $dto->brokerPaymentMethodId = (int)$args['broker_payment_method_id'];
            $dto->brokerPaymentDays = (int)$args['broker_payment_days'];
            $dto->brokerPaymentBegins = $args['broker_payment_begins'];
        }

        if (array_key_exists('broker_fee_amount', $args) && !empty($args['broker_fee_amount'])) {
            $dto->brokerFeeAmount = (float)$args['broker_fee_amount'];
            $dto->brokerFeeMethodId = (int)$args['broker_fee_method_id'];
            $dto->brokerFeeDays = (int)$args['broker_fee_days'];
            $dto->brokerFeeBegins = $args['broker_fee_begins'];
        }

        return $dto;
    }
}
