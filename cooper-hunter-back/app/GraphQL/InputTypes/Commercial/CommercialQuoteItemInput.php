<?php

namespace App\GraphQL\InputTypes\Commercial;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Products\Product;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class CommercialQuoteItemInput extends BaseInputType
{
    public const NAME = 'CommercialQuoteItemInput';

    public function fields(): array
    {
        return [
            'product_id' => [
                'type' => Type::id(),
                'rules' => [
                    'nullable',
                    Rule::exists(Product::class, 'id'),
                ],
            ],
            'name' => [
                'type' => Type::string(),
                'rules' => [
                    'nullable', 'string', 'max:250',
                ],
            ],
            'qty' => [
                'type' => NonNullType::int(),
            ],
            'price' => [
                'type' => NonNullType::float(),
            ],
        ];
    }
}
