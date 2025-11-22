<?php

namespace App\GraphQL\Types\Commercial;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Commercial\CommercialQuoteStatusEnumType;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;
use App\Models\Commercial\CommercialQuote;
use Core\Traits\Auth\AuthGuardsTrait;

class CommercialQuoteType extends BaseType
{
    use AuthGuardsTrait;

    public const NAME = 'CommercialQuoteType';
    public const MODEL = CommercialQuote::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'status' => [
                    'type' => CommercialQuoteStatusEnumType::nonNullType(),
                ],
                'email' => [
                    'type' => NonNullType::string(),
                ],
                'shipping_address' => [
                    'type' => NonNullType::string(),
                ],
                'commercial_project' => [
                    'type' => CommercialProjectType::type(),
                    'is_relation' => true,
                    'alias' => 'commercialProject',
                ],
                'file' => [
                    'type' => MediaType::nonNullType(),
                    'alias' => 'media',
                    'always' => 'id',
                    'resolve' => static fn(CommercialQuote $m) => $m->getFirstMedia(CommercialQuote::MEDIA_COLLECTION_NAME)
                ],
                'items' => [
                    'type' => CommercialQuoteItemType::list(),
                    'is_relation' => true,
                ],
                'histories' => [
                    'type' => CommercialQuoteHistoryType::list(),
                    'is_relation' => true,
                ],
                'send_detail_data' => [
                    'type' => Type::boolean(),
                ],
                'closed_at' => [
                    'type' => Type::string(),
                ],
                'shipping_price' => [
                    'type' => Type::float(),
                ],
                'tax' => [
                    'type' => Type::float(),
                ],
                'discount_percent' => [
                    'type' => Type::float(),
                ],
                'discount_sum' => [
                    'type' => Type::float(),
                ],
                'sub_total' => [
                    'type' => Type::float(),
                    "description" => 'Общая сумма по всем item',
                    'resolve' => static fn (CommercialQuote $model) => $model->sub_total
                ],
                'discount' => [
                    'type' => Type::float(),
                    "description" => 'Скидка',
                    'resolve' => static fn (CommercialQuote $model) => $model->discount
                ],
                'tax_sum' => [
                    'type' => Type::float(),
                    "description" => 'Сумма налога',
                    'resolve' => static fn (CommercialQuote $model) => $model->tax_sum
                ],
                'total' => [
                    'type' => Type::float(),
                    "description" => 'Общая сумма к оплате',
                    'resolve' => static fn (CommercialQuote $model) => $model->total
                ],
            ],
        );
    }
}
