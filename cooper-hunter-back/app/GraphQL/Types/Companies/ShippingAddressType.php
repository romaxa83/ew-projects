<?php

namespace App\GraphQL\Types\Companies;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Locations\CountryType;
use App\GraphQL\Types\Locations\StateType;
use App\GraphQL\Types\NonNullType;
use App\Models\Companies\ShippingAddress;
use GraphQL\Type\Definition\Type;

class ShippingAddressType extends BaseType
{
    public const NAME = 'companyShippingAddressType';
    public const MODEL = ShippingAddress::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::id()),
            ],
            'name' => [
                'type' => NonNullType::string(),
            ],
            'active' => [
                'type' => NonNullType::boolean(),
            ],
            'phone' => [
                'type' => NonNullType::string(),
            ],
            'fax' => [
                'type' => Type::string(),
            ],
            'email' => [
                'type' => Type::string(),
            ],
            'receiving_persona' => [
                'type' => Type::string(),
            ],
            'country' => [
                'type' => CountryType::type(),
                'is_relation' => true,
            ],
            'state' => [
                'type' => StateType::type(),
                'is_relation' => true,
            ],
            'city' => [
                'type' => NonNullType::string(),
            ],
            'address_line_1' => [
                'type' => NonNullType::string(),
            ],
            'address_line_2' => [
                'type' => Type::string(),
            ],
            'zip' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}

