<?php

namespace App\Dto\Inventories;

use App\Enums\Inventories\Transaction\OperationType;
use App\Foundations\ValueObjects\Email;
use App\Foundations\ValueObjects\Phone;

class SoldDto
{
    public string $operationType;
    public float $qty;
    public float $price;
    public string $transactionDate;
    public string|null $invoiceNumber;
    public string $describe;
    public float|null $discount;
    public float|null $tax;
    public string|null $paymentDate;
    public string|null $paymentMethod;
    public string|null $firstName;
    public string|null $lastName;
    public string|null $companyName;
    public Phone|null $phone;
    public Email|null $email;

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->operationType = OperationType::SOLD->value;
        $self->qty = $data['quantity'];
        $self->price = $data['price'];
        $self->transactionDate = $data['date'];
        $self->invoiceNumber = $data['invoice_number'] ?? null;

        $self->describe = $data['describe'] ?? null;
        $self->discount = $data['discount'] ?? null;
        $self->tax = $data['tax'] ?? null;
        $self->paymentDate = $data['payment_date'] ?? null;
        $self->paymentMethod = $data['payment_method'] ?? null;
        $self->firstName = $data['first_name'] ?? null;
        $self->lastName = $data['last_name'] ?? null;
        $self->companyName = $data['company_name'] ?? null;
        $self->phone = isset($data['phone'])
            ? new Phone($data['phone'])
            : null;
        $self->email = isset($data['email'])
            ? new Email($data['email'])
            : null;

        return $self;
    }
}
