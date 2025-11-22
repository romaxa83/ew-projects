<?php

namespace App\Dto\Commercial;

use App\Repositories\Catalog\Product\ProductRepository;

class CommercialProjectUnitDto
{
    public $serialNumbers = [];
    public $productId;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->serialNumbers = $args['serial_numbers'];
        $self->productId = self::productIDFromDB($args['product_guid']);

        return $self;
    }

    private static function productIDFromDB($guid): int
    {
        return resolve(ProductRepository::class)->getByFieldsObj(['guid' => $guid], ['id'])->id;
    }
}


