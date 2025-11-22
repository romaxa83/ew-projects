<?php

namespace App\GraphQL\Types\Catalog\Videos\Links;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Videos\VideoLinkTranslation;
use GraphQL\Type\Definition\Type;

class TranslateType extends BaseType
{
    public const NAME = 'VideoLinkTranslateType';
    public const MODEL = VideoLinkTranslation::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'title' => [
                'type' => NonNullType::string(),
            ],
            'slug' => [
                'type' => NonNullType::string(),
            ],
            'description' => [
                'type' => Type::string(),
            ],
            'language' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}


