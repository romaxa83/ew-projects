<?php

namespace App\GraphQL\Types\Orders\Deliveries;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Orders\Deliveries\OrderDeliveryTypeTranslation;
use GraphQL\Type\Definition\Type;

class OrderDeliveryTypeTranslateType extends BaseType
{
    public const NAME = 'OrderDeliveryTypeTranslateType';
    public const MODEL = OrderDeliveryTypeTranslation::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'title' => [
                'type' => NonNullType::string(),
            ],
            'slug' => [
                'type' => NonNullType::string(),
            ],
            'description' => [
                'type' => Type::string(),
            ],
            'language' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
