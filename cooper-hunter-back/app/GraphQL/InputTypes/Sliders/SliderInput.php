<?php

namespace App\GraphQL\InputTypes\Sliders;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Rules\TranslationsArrayValidator;
use GraphQL\Type\Definition\Type;

class SliderInput extends BaseInputType
{
    public const NAME = 'SliderInput';

    public function fields(): array
    {
        return [
            'active' => [
                'type' => NonNullType::boolean(),
            ],
            'link' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'string', 'url'],
            ],
            'translations' => [
                'type' => SliderTranslationInput::nonNullList(),
                'rules' => [new TranslationsArrayValidator()],
            ],
        ];
    }
}
