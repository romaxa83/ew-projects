<?php

namespace App\GraphQL\InputTypes\Commercial\Commissioning;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Commercial\Commissioning\OptionAnswerTranslationInputType;
use App\Rules\TranslationsArrayValidator;

class OptionsAnswerUpdateInput extends BaseInputType
{
    public const NAME = 'CommissioningOptionAnswerUpdateInput';

    public function fields(): array
    {
        return [
            'translations' => [
                'type' => OptionAnswerTranslationInputType::nonNullList(),
                'rules' => [
                    'required',
                    'array',
                    new TranslationsArrayValidator()
                ]
            ],
        ];
    }
}




