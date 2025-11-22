<?php

namespace App\Dto\Commercial;

class CommercialQuoteItemDto
{
    private ?int $productId;
    private ?string $name;
    private int $qty;
    private float $price;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->productId = data_get($args, 'product_id');
        $dto->name = data_get($args, 'name');
        $dto->qty = data_get($args, 'qty');
        $dto->price = (float)data_get($args, 'price');

        return $dto;
    }

    public function getProductID(): ?int
    {
        return $this->productId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getQty(): int
    {
        return $this->qty;
    }

    public function getPrice(): float
    {
        return $this->price;
    }
}

