<?php

namespace App\Dto\Orders;


class OrderPartDto
{

    private int $id;

    private ?string $description;

    private int $quantity;

    private ?float $price = null;

    public static function byArgs(array $args): OrderPartDto
    {
        $dto = new self();

        $dto->id = $args['id'];
        $dto->description = data_get($args, 'description');
        $dto->quantity = config('orders.categories.default_quantity');

        if (($price = data_get($args, 'price')) !== null) {
            $dto->price = (float)$price;
        }

        return $dto;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function getPart(): array
    {
        return [
            'id' => $this->getId(),
            'description' => $this->getDescription(),
            'quantity' => $this->getQuantity(),
            'price' => $this->getPrice(),
        ];
    }
}
