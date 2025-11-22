<?php

namespace App\GraphQL\Types\Dictionaries;

use App\Entities\Dictionaries\DictionaryItem;
use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Dictionaries\DictionaryEnumType;
use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;

class DictionaryType extends BaseType
{
    public const NAME = 'DictionaryType';

    public function fields(): array
    {
        return [
            'name' => [
                'type' =>DictionaryEnumType::nonNullType(),
            ],
            'count' => [
                'type' => NonNullType::int(),
            ],
            'updated_at' => [
                'type' => Type::int(),
                'resolve' => static fn(DictionaryItem $item) => $item->getUpdatedAt(),
                'description' => 'UNIX time',
            ]
        ];
    }
}
