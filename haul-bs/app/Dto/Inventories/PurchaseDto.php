<?php

namespace App\Dto\Inventories;

use App\Enums\Inventories\Transaction\OperationType;

class PurchaseDto
{
    public string $operationType;
    public float $qty;
    public float $price;
    public string $transactionDate;
    public string|null $invoiceNumber;

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->operationType = OperationType::PURCHASE->value;
        $self->qty = $data['quantity'];
        $self->price = $data['cost'];
        $self->transactionDate = $data['date'];
        $self->invoiceNumber = $data['invoice_number'] ?? null;

        return $self;
    }
}
