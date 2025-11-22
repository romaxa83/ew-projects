<?php

namespace App\GraphQL\InputTypes\Commercial;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\Commercial\CommercialQuoteStatusEnumType;
use App\Rules\OneFieldsRequired;
use GraphQL\Type\Definition\Type;

class CommercialQuoteAdminInput extends BaseInputType
{
    public const NAME = 'CommercialQuoteAdminInput';

    public function fields(): array
    {
        return [
            'status' => [
                'type' => CommercialQuoteStatusEnumType::Type(),
                'description' => 'Status',
            ],
            'email' => [
                'type' => Type::string(),
                'rules' => ['email:filter'],
            ],
            'send_detail_data' => [
                'type' => Type::boolean()
            ],
            'shipping_price' => [
                'type' => Type::float()
            ],
            'tax' => [
                'type' => Type::float()
            ],
            'discount_percent' => [
                'type' => Type::float()
            ],
            'discount_sum' => [
                'type' => Type::float()
            ],
            'items' => [
                'type' => CommercialQuoteItemInput::nonNullList(),
                'rules' => ['nullable', 'array', new OneFieldsRequired('name', 'product_id')]
            ],
        ];
    }
}

