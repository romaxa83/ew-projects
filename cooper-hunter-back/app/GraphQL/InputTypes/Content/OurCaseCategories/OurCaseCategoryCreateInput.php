<?php

namespace App\GraphQL\InputTypes\Content\OurCaseCategories;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Rules\TranslationsArrayValidator;

class OurCaseCategoryCreateInput extends BaseInputType
{
    public const NAME = 'OurCaseCategoryCreateInput';

    public function fields(): array
    {
        return [
            'slug' => [
                'type' => NonNullType::string(),
            ],
            'active' => [
                'type' => NonNullType::boolean(),
            ],
            'translations' => [
                'type' => OurCaseCategoryTranslationInput::nonNullList(),
                'rules' => [new TranslationsArrayValidator()],
            ],
        ];
    }
}
