<?php

namespace App\GraphQL\Types\Dictionaries;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Localization\LanguageEnumType;
use App\GraphQL\Types\NonNullType;

abstract class BaseDictionaryTranslateType extends BaseType
{
    public function fields(): array
    {
        return [
            'title' => [
                'type' => NonNullType::string(),
            ],
            'language' => [
                'type' => LanguageEnumType::nonNullType(),
            ],
        ];
    }
}
