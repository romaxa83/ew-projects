<?php

namespace App\GraphQL\Types\Commercial;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\QuoteItem;
use Core\Traits\Auth\AuthGuardsTrait;
use GraphQL\Type\Definition\Type;

class CommercialQuoteItemType extends BaseType
{
    use AuthGuardsTrait;

    public const NAME = 'CommercialQuoteItemType';
    public const MODEL = QuoteItem::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'name' => [
                    'type' => NonNullType::string(),
                    'always' => ['product_id', 'name'],
                    'resolve' => function (QuoteItem $model) {
                        return $model->title;
                    }
                ],
                'qty' => [
                    'type' => NonNullType::int(),
                ],
                'price' => [
                    'type' => NonNullType::float(),
                ],
                'total' => [
                    'type' => NonNullType::float(),
                    "description" => 'Общая сумма для позиции (price * qty)',
                    'resolve' => fn (QuoteItem $model) => $model->total
                ],
            ],
        );
    }
}
