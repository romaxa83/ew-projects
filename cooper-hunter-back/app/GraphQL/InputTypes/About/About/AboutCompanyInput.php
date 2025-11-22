<?php

namespace App\GraphQL\InputTypes\About\About;

use App\GraphQL\Types\BaseInputType;
use App\Rules\TranslationsArrayValidator;

class AboutCompanyInput extends BaseInputType
{
    public const NAME = 'AboutCompanyInput';

    public function fields(): array
    {
        return [
            'translations' => [
                'type' => AboutCompanyTranslationInput::nonNullList(),
                'rules' => [new TranslationsArrayValidator()],
            ],
        ];
    }
}
