<?php

namespace App\GraphQL\Types\Localization;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Localization\LanguageEnumType;
use App\GraphQL\Types\NonNullType;

class TranslateType extends BaseType
{
    public const NAME = 'TranslateType';

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'place' => [
                    'type' => NonNullType::string(),
                ],
                'key' => [
                    'type' => NonNullType::string(),
                ],
                'text' => [
                    'type' => NonNullType::string(),
                ],
                'lang' => [
                    'type' => LanguageEnumType::nonNullType(),
                ],
            ]
        );
    }
}
