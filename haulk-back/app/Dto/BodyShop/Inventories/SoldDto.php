<?php

namespace App\Dto\BodyShop\Inventories;

use App\Models\BodyShop\Inventories\Transaction;

class SoldDto implements TransactionDto
{
    private array $data;

    public static function byParams(array $data): self
    {
        $dto = new self();

        $dto->data = [
            'operation_type' => Transaction::OPERATION_TYPE_SOLD,
            'quantity' => $data['quantity'],
            'price' => $data['price'],
            'transaction_date' => $data['date'],
            'describe' => $data['describe'],
        ];

        if ($data['describe'] === Transaction::DESCRIBE_SOLD) {
            $dto->data = array_merge(
                $dto->data,
                [
                    'discount' => $data['discount'] ?? null,
                    'tax' => $data['tax'] ?? null,
                    'invoice_number' => $data['invoice_number'],
                    'payment_date' => $data['payment_date'],
                    'payment_method' => $data['payment_method'],
                    'first_name' => $data['first_name'] ?? null,
                    'last_name' => $data['last_name'] ?? null,
                    'company_name' => $data['company_name'] ?? null,
                    'phone' => $data['phone'],
                    'email' => $data['email'],
                ]
            );
        }

        return $dto;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
