<?php

namespace App\GraphQL\Types\About;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
use App\Models\About\AboutCompany;
use App\Models\Media\Media;

class AboutCompanyType extends BaseType
{
    public const NAME = 'AboutCompanyType';
    public const MODEL = AboutCompany::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'video' => [
                'type' => MediaType::type(),
                'always' => 'id',
                'alias' => 'media',
                'query' => fn(array $args, $query, $ctx) => $query
                    ->where(Media::TABLE . '.collection_name', AboutCompany::MEDIA_SHORT_VIDEO),
                'resolve' => fn(AboutCompany $a) => $a->getFirstMedia($a::MEDIA_SHORT_VIDEO),
            ],
            'video_preview' => [
                'type' => MediaType::type(),
                'always' => 'id',
                'alias' => 'media',
                'resolve' => fn(AboutCompany $a) => $a->getFirstMedia($a::VIDEO_PREVIEW),
            ],
            'additional_video' => [
                'type' => MediaType::type(),
                'always' => 'id',
                'alias' => 'media',
                'query' => fn(array $args, $query, $ctx) => $query
                    ->where(Media::TABLE . '.collection_name', AboutCompany::ADDITIONAL_MEDIA_SHORT_VIDEO),
                'resolve' => fn(AboutCompany $a) => $a->getFirstMedia($a::ADDITIONAL_MEDIA_SHORT_VIDEO),
            ],
            'additional_video_preview' => [
                'type' => MediaType::type(),
                'always' => 'id',
                'alias' => 'media',
                'resolve' => fn(AboutCompany $a) => $a->getFirstMedia($a::ADDITIONAL_VIDEO_PREVIEW),
            ],
            'images' => [
                'type' => MediaType::list(),
                'always' => 'id',
                'alias' => 'media',
                'resolve' => fn(AboutCompany $a) => $a->getMedia($a::MEDIA_COLLECTION_NAME),
            ],
            'translation' => [
                'type' => AboutCompanyTranslationType::nonNullType(),
            ],
            'translations' => [
                'type' => AboutCompanyTranslationType::nonNullList(),
            ],
        ];
    }
}
