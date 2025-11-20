<?php

namespace App\GraphQL\Types\Localization;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;

class LanguageType extends BaseType
{
    public const NAME = 'LanguageType';

    public function fields(): array
    {
        return [
            'name' => [
                'type' => NonNullType::string(),
            ],

            'slug' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
