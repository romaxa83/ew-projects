<?php

namespace App\GraphQL\Types\Orders\Dealer;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Catalog\Products\ProductType;
use App\GraphQL\Types\NonNullType;
use App\Models\Orders\Dealer\Item;
use GraphQL\Type\Definition\Type;

class ItemType extends BaseType
{
    public const NAME = 'dealerOrderItemType';
    public const MODEL = Item::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'product' => [
                    'type' => ProductType::nonNullType(),
                ],
                'price' => [
                    'type' => NonNullType::float(),
                ],
                'discount' => [
                    'type' => NonNullType::float(),
                ],
                'discount_total' => [
                    'type' => NonNullType::float(),
                ],
                'qty' => [
                    'type' => NonNullType::int(),
                ],
                'amount' => [
                    'type' => NonNullType::float(),
                    'selectable' => false,
                    'resolve' => fn (Item $model) => $model->amount_with_discount
                ],
                'total' => [
                    'type' => NonNullType::float(),
                ],
                'description' => [
                    'type' => Type::string(),
                ],
            ]
        );
    }
}
