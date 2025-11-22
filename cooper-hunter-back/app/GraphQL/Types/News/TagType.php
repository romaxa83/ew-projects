<?php

namespace App\GraphQL\Types\News;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\News\Tag;

class TagType extends BaseType
{
    public const NAME = 'Tag';
    public const MODEL = Tag::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'color' => [
                'type' => NonNullType::string(),
                'description' => 'Hex value of color. Example: "#FFF;"'
            ],
            'translation' => [
                'type' => TagTranslationType::nonNullType(),
            ],
        ];
    }
}
