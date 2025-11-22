<?php

namespace App\GraphQL\InputTypes\Dealers;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;

class DealerInput extends BaseInputType
{
    public const NAME = 'DealerInput';

    public function fields(): array
    {
        return [
            'company_id' => [
                'type' => NonNullType::id(),
            ],
            'email' => [
                'type' => NonNullType::string(),
            ],
            'name' => [
                'type' => NonNullType::string(),
            ],
            'shipping_address_ids' => [
                'type' => NonNullType::listOf(Type::id())
            ]
        ];
    }
}
