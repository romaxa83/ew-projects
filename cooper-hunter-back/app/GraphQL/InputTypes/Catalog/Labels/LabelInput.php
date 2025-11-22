<?php

namespace App\GraphQL\InputTypes\Catalog\Labels;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\Catalog\Labels\ColorTypeEnumType;
use App\Rules\TranslationsArrayValidator;

class LabelInput extends BaseInputType
{
    public const NAME = 'LabelInputType';

    public function fields(): array
    {
        return [
            'color_type' => [
                'type' => ColorTypeEnumType::nonNullType(),
            ],
            'translations' => [
                'type' => LabelTranslationInputType::nonNullList(),
                'rules' => [
                    'required',
                    'array',
                    new TranslationsArrayValidator()
                ]
            ],
        ];
    }
}
