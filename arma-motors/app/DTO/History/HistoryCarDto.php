<?php

namespace App\DTO\History;

final class HistoryCarDto
{
    public null|string $uuid_car;

    public array $invoices = [];
    public array $orders = [];

    private function __construct()
    {}

    public static function byRequest(array $data): self
    {
        $self = new self();

        $self->uuid_car = $data['id'] ?? null;

        foreach ($data['invoices'] ?? [] as  $invoice){
            $self->invoices[] = InvoiceDto::byRequest($invoice);
        }

        foreach ($data['orders'] ?? [] as  $order){
            $self->orders[] = OrderDto::byRequest($order);
        }

        return $self;
    }
}

