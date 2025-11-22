<?php

namespace App\GraphQL\Types\News;

use App\GraphQL\Types\BaseTranslationType;
use App\GraphQL\Types\NonNullType;
use App\Models\News\VideoTranslation;
use GraphQL\Type\Definition\Type;

class VideoTranslationType extends BaseTranslationType
{
    public const NAME = 'VideoTranslationType';
    public const MODEL = VideoTranslation::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'video_link' => [
                    'type' => NonNullType::string(),
                ],
                'title' => [
                    'type' => NonNullType::string(),
                ],
                'description' => [
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
