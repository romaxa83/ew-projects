<?php

namespace App\GraphQL\InputTypes\Dictionaries;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\Localization\LanguageEnumType;
use App\GraphQL\Types\NonNullType;

abstract class BaseDictionaryTranslateInputType extends BaseInputType
{
    public function fields(): array
    {
        return [
            'language' => [
                'type' => LanguageEnumType::nonNullType(),
            ],
            'title' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
