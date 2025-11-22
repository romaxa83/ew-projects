<?php

namespace App\GraphQL\Types\Orders\Deliveries;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Orders\Deliveries\OrderDeliveryType;

class OrderDeliveryTypeType extends BaseType
{
    public const NAME = 'OrderDeliveryTypeType';
    public const MODEL = OrderDeliveryType::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'sort' => [
                    'type' => NonNullType::int()
                ],
                'active' => [
                    'type' => NonNullType::boolean()
                ],
                'translation' => [
                    'type' => OrderDeliveryTypeTranslateType::nonNullType(),
                    'is_relation' => true,
                ],
                'translations' => [
                    'type' => OrderDeliveryTypeTranslateType::nonNullList(),
                    'is_relation' => true,
                ],
            ]
        );
    }


}
