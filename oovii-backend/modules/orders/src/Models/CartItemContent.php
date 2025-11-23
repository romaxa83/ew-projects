<?php

namespace WezomCms\Orders\Models;

/**
 * WezomCms\Orders\Models\CartItemContent
 * @property int $id
 * @property string $uniqueId
 * @property float $price
 * @property float $quantity
 * @property array $options
 */
class CartItemContent
{
    private ?string $uniqueId;
    private ?int $id;
    private ?float $price;
    private ?float $quantity;
    private ?array $options;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->uniqueId = data_get($args, 'unique_id');
        $self->id = data_get($args, 'id');
        $self->price = data_get($args, 'price');
        $self->quantity = data_get($args, 'quantity');
        $self->options = data_get($args, 'options');

        return $self;
    }

    public function getUniqueId(): ?string
    {
        return $this->uniqueId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
