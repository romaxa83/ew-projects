<?php

namespace App\Dto\Orders\Dealer;

class OrderInvoiceOnecDto
{
    public ?float $tax;
    public ?float $shippingPrice;
    public ?float $total;
    public ?float $totalDiscount;
    public ?float $totalWithDiscount;

    public ?string $invoice;
    public ?string $invoiceAt;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->tax = data_get($args, 'tax', 0);
        $dto->shippingPrice = data_get($args, 'shipping_price', 0);
        $dto->total = data_get($args, 'total', 0);
        $dto->totalDiscount = data_get($args, 'total_discount', 0);
        $dto->totalWithDiscount = data_get($args, 'total_with_discount', 0);

        $dto->invoice = data_get($args, 'invoice');
        $dto->invoiceAt = data_get($args, 'invoice_date');

        return $dto;
    }
}
