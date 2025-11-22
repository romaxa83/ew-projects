<?php

namespace App\GraphQL\InputTypes\Catalog\Features\Specifications;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Rules\TranslationsArrayValidator;

class SpecificationCreateInput extends BaseInputType
{
    public const NAME = 'SpecificationCreateInput';

    public function fields(): array
    {
        return [
            'active' => [
                'type' => NonNullType::boolean(),
            ],
            'icon' => [
                'type' => NonNullType::string(),
            ],
            'translations' => [
                'type' => SpecificationTranslationInput::nonNullList(),
                'rules' => [new TranslationsArrayValidator()],
            ],
        ];
    }
}
