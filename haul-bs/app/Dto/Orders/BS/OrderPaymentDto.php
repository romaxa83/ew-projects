<?php

namespace App\Dto\Orders\BS;

class OrderPaymentDto
{
    public float $amount;
    public string $paymentDate;
    public string $paymentMethod;
    public string|null $notes;
    public string|null $referenceNumber;

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->amount = $data['amount'];
        $self->paymentDate = $data['payment_date'];
        $self->paymentMethod = $data['payment_method'];
        $self->notes = $data['notes'] ?? null;
        $self->referenceNumber = $data['reference_number'] ?? null;

        return $self;
    }
}
