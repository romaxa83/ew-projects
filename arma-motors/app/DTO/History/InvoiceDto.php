<?php

namespace App\DTO\History;

use App\DTO\Order\OrderPdfDTO;
use App\Models\Order\Order;

final class InvoiceDto
{
    public null|string $aa_id;
    public null|string $address;
    public null|float $amount_including_vat;
    public null|float $amount_vat;
    public null|float $amount_without_vat;
    public null|string $author;
    public null|string $contact_information;
    public null|string $date;
    public null|float $discount;
    public null|string $etc;
    public null|string $number;
    public null|string $organization;
    public null|string $phone;
    public null|string $shopper;
    public null|string $tax_code;

    public array $parts = [];

    public $pdfData;

    private function __construct()
    {}

    public static function byRequest(array $data): self
    {
        $self = new self();

        $self->aa_id = $data['id'] ?? null;
        $self->address = $data['address'] ?? null;
        $self->amount_including_vat = $data['amountIncludingVAT'] ?? null;
        $self->amount_vat = $data['amountVAT'] ?? null;
        $self->amount_without_vat = $data['amountWithoutVAT'] ?? null;
        $self->author = $data['author'] ?? null;
        $self->contact_information = $data['contactInformation'] ?? null;
        $self->date = $data['date'] ?? null;
        $self->discount = $data['discount'] ?? null;
        $self->etc = $data['etc'] ?? null;
        $self->number = $data['number'] ?? null;
        $self->organization = $data['organization'] ?? null;
        $self->phone = $data['phone'] ?? null;
        $self->shopper = $data['shopper'] ?? null;
        $self->tax_code = $data['taxCode'] ?? null;

        foreach ($data['parts'] ?? [] as  $part){
            $self->parts[] = InvoicePartDto::byRequest($part);
        }

        $self->pdfData = app(OrderPdfDTO::class)->fill($data, Order::FILE_BILL_TYPE);

        return $self;
    }
}

