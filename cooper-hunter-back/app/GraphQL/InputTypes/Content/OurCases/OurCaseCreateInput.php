<?php

namespace App\GraphQL\InputTypes\Content\OurCases;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Products\Product;
use App\Models\Content\OurCases\OurCaseCategory;
use App\Rules\TranslationsArrayValidator;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class OurCaseCreateInput extends BaseInputType
{
    public const NAME = 'OurCaseCreateInput';

    public function fields(): array
    {
        return [
            'our_case_category_id' => [
                'type' => NonNullType::id(),
                'rules' => [Rule::exists(OurCaseCategory::TABLE, 'id')],
            ],
            'active' => [
                'type' => NonNullType::boolean(),
            ],
            'translations' => [
                'type' => OurCaseTranslationInput::nonNullList(),
                'rules' => [new TranslationsArrayValidator()],
            ],
            'product_ids' => [
                'type' => Type::listOf(Type::id()),
                'rules' => ['array', Rule::exists(Product::TABLE, 'id')]
            ],
        ];
    }
}
