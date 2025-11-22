<?php

namespace App\GraphQL\Types\Localization;

use App\GraphQL\Types\BaseType;
use GraphQL\Type\Definition\Type;
use JetBrains\PhpStorm\ArrayShape;

class LanguageType extends BaseType
{
    public const NAME = 'LanguageType';

    public function fields(): array
    {
        return [
            'name' => [
                'type' => Type::nonNull(Type::string()),
            ],

            'slug' => [
                'type' => Type::nonNull(Type::string()),
            ],
        ];
    }
}
