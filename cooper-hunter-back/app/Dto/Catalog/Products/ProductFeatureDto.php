<?php

namespace App\Dto\Catalog\Products;

class ProductFeatureDto
{
    private int $valueId;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->valueId = $args['value_id'];

        return $self;
    }

    public function getValueId(): int
    {
        return $this->valueId;
    }
}
