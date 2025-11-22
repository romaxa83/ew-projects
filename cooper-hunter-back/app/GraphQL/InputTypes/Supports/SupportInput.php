<?php

namespace App\GraphQL\InputTypes\Supports;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Rules\PhoneRule;
use App\Rules\TranslationsArrayValidator;

class SupportInput extends BaseInputType
{
    public const NAME = 'SupportInput';

    public function fields(): array
    {
        return [
            'phone' => [
                'type' => NonNullType::string(),
                'rules' => [new PhoneRule()]
            ],
            'translations' => [
                'type' => SupportTranslationInput::nonNullList(),
                'rules' => [new TranslationsArrayValidator()],
            ],
        ];
    }
}
