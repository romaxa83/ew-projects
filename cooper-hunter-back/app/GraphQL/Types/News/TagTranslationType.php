<?php

namespace App\GraphQL\Types\News;

use App\GraphQL\Types\BaseTranslationType;
use App\GraphQL\Types\NonNullType;
use App\Models\News\TagTranslation;

class TagTranslationType extends BaseTranslationType
{
    public const NAME = 'TagTranslationType';
    public const MODEL = TagTranslation::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'title' => [
                    'type' => NonNullType::string(),
                ],
            ]
        );
    }
}
