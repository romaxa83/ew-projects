<?php

namespace App\GraphQL\Types\Orders\Dealer;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Catalog\Products\ProductType;
use App\GraphQL\Types\NonNullType;
use App\Models\Orders\Dealer\PackingSlipItem;
use GraphQL\Type\Definition\Type;

class PackingSlipItemType extends BaseType
{
    public const NAME = 'dealerOrderPackingSlipItemType';
    public const MODEL = PackingSlipItem::class;

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
                    'selectable' => false,
                    'always' => ['id', 'order_item_id'],
                    'resolve' => fn (PackingSlipItem $model) => $model->orderItem->price
                ],
                'discount' => [
                    'type' => NonNullType::float(),
                    'selectable' => false,
                    'always' => ['id', 'order_item_id'],
                    'resolve' => fn (PackingSlipItem $model) => $model->orderItem->discount
                ],
                'discount_total' => [
                    'type' => NonNullType::float(),
                    'selectable' => false,
                    'always' => ['id', 'order_item_id'],
                    'resolve' => fn (PackingSlipItem $model) => $model->orderItem->discount_total
                ],
                'qty' => [
                    'type' => NonNullType::int(),
                ],
                'amount' => [
                    'type' => NonNullType::float(),
                    'selectable' => false,
                    'resolve' => fn (PackingSlipItem $model) => $model->amount
                ],
                'total' => [
                    'type' => NonNullType::float(),
                    'selectable' => false,
                    'always' => ['id', 'order_item_id'],
                    'resolve' => fn (PackingSlipItem $model) => $model->total
                ],
                'description' => [
                    'type' => Type::string(),
                ],
            ]
        );
    }
}
