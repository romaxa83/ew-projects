<?php

namespace App\GraphQL\Types\Stores;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Locations\StateType;
use App\GraphQL\Types\NonNullType;
use App\Models\Stores\Distributor;
use GraphQL\Type\Definition\Type;

class DistributorType extends BaseType
{
    public const NAME = 'Distributor';
    public const MODEL = Distributor::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'state' => [
                'type' => StateType::type(),
                'is_relation' => true,
            ],
            'active' => [
                'type' => NonNullType::boolean(),
            ],
            'coordinates' => [
                'type' => CoordinateType::nonNullType(),
                'is_relation' => false,
            ],
            'address' => [
                'type' => NonNullType::string(),
            ],
            'link' => [
                'type' => Type::string(),
            ],
            'phone' => [
                'type' => Type::string(),
            ],
            'translation' => [
                'type' => DistributorTranslationType::nonNullType(),
            ],
            'translations' => [
                'type' => DistributorTranslationType::nonNullList(),
            ],
        ];
    }
}
