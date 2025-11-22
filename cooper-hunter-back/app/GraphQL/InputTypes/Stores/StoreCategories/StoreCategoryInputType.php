<?php

namespace App\GraphQL\InputTypes\Stores\StoreCategories;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Rules\TranslationsArrayValidator;

class StoreCategoryInputType extends BaseInputType
{
    public const NAME = 'StoreCategoryInputType';

    public function fields(): array
    {
        return [
            'active' => [
                'type' => NonNullType::boolean(),
            ],
            'translations' => [
                'type' => StoreCategoryTranslationInput::nonNullList(),
                'rules' => [new TranslationsArrayValidator()],
            ],
        ];
    }
}
