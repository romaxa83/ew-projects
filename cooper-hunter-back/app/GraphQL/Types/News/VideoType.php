<?php

namespace App\GraphQL\Types\News;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\News\Video;
use App\Traits\GraphQL\HasNextPrevLinks;

class VideoType extends BaseType
{
    use HasNextPrevLinks;

    public const NAME = 'VideoType';
    public const MODEL = Video::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            $this->getNextPrevLinks(),
            [
                'active' => [
                    'type' => NonNullType::boolean(),
                ],
                'translation' => [
                    'type' => VideoTranslationType::nonNullType(),
                ],
                'translations' => [
                    'type' => VideoTranslationType::nonNullList(),
                ],
                'slug' => [
                    'type' => NonNullType::string()
                ],
            ]
        );
    }
}
