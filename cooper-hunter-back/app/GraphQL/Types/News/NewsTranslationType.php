<?php

namespace App\GraphQL\Types\News;

use App\GraphQL\Types\BaseTranslationType;
use App\GraphQL\Types\NonNullType;
use App\Models\News\NewsTranslation;
use GraphQL\Type\Definition\Type;

class NewsTranslationType extends BaseTranslationType
{
    public const NAME = 'NewsTranslationType';
    public const MODEL = NewsTranslation::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'title' => [
                    'type' => NonNullType::string(),
                ],
                'description' => [
                    'type' => NonNullType::string(),
                ],
                'short_description' => [
                    'type' => NonNullType::string(),
                ],
                'seo_title' => [
                    'type' => Type::string(),
                ],
                'seo_description' => [
                    'type' => Type::string(),
                ],
                'seo_h1' => [
                    'type' => Type::string(),
                ],
            ]
        );
    }
}
