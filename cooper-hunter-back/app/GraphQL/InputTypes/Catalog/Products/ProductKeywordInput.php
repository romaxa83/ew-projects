<?php

namespace App\GraphQL\InputTypes\Catalog\Products;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Products\Product;
use Illuminate\Validation\Rule;

class ProductKeywordInput extends BaseInputType
{
    public const NAME = 'ProductKeywordInput';

    public function fields(): array
    {
        return [
            'product_id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Product::class, 'id')],
            ],
            'keyword' => [
                'type' => NonNullType::string(),
                'rules' => ['required', 'string', 'max:255'],
            ],
        ];
    }
}