<?php

namespace App\GraphQL\Types\Orders\Dealer;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Catalog\Products\ProductOwnerTypeEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Products\Product;
use GraphQL\Type\Definition\Type;

class ProductType extends BaseType
{
    public const NAME = 'dealerOrderProductType';
    public const MODEL = Product::class;

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
            'price' => [
                'type' => NonNullType::float(),
            ],
            'price_description' => [
                'type' => Type::string(),
                'description' => 'Описание пришедшее вместе с ценой, от 1С'
            ],
            'owner_type' => [
                'type' => ProductOwnerTypeEnumType::nonNullType()
            ],
            'category_id' => [
                'type' => NonNullType::id(),
            ],
            'brand' => [
                'type' => NonNullType::string(),
            ],
            'img' => [
                'type' => Type::string(),
            ],
            'is_added' => [
                'type' => Type::boolean(),
                'description' => 'Добавлен ли товар в заказ'
            ],
            'accessories' => [
                'type' => self::list(),
                'is_relation' => false,
                'description' => 'Товары привязанные к товару'
            ],
        ];
    }
}
