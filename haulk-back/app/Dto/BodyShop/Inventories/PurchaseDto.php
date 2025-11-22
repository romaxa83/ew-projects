<?php

namespace App\Dto\BodyShop\Inventories;

use App\Models\BodyShop\Inventories\Transaction;

class PurchaseDto implements TransactionDto
{
    private array $data;

    public static function byParams(array $data): self
    {
        $dto = new self();

        $dto->data = [
            'operation_type' => Transaction::OPERATION_TYPE_PURCHASE,
            'quantity' => $data['quantity'],
            'price' => $data['cost'],
            'transaction_date' => $data['date'],
            'invoice_number' => $data['invoice_number'] ?? null,
        ];

        return $dto;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
