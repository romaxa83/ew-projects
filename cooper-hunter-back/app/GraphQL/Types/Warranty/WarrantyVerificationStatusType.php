<?php

namespace App\GraphQL\Types\Warranty;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Catalog\Products\SimpleProductType;
use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;

class WarrantyVerificationStatusType extends BaseType
{
    public const NAME = 'WarrantyVerificationStatusType';

    public function fields(): array
    {
        return [
            'is_registered' => [
                'type' => NonNullType::boolean(),
                'description' => 'Determine if product name (link) should be replaced in "information" field.\\n If "true" then the "product" field will be present',
            ],
            'information' => [
                'type' => NonNullType::string(),
                'description' => 'If "is_registered" fields is "true", so "#product#" pattern should be replaced with product link and name',
            ],
            'product' => [
                'type' => SimpleProductType::type(),
                'description' => 'If "is_registered" is true,  then the product information can be found here',
            ],
            'purchase_date' => [
                'type' => Type::string(),
            ],
            'installation_date' => [
                'type' => Type::string(),
            ],
        ];
    }
}
