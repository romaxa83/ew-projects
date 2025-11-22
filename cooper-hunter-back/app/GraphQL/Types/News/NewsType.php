<?php

namespace App\GraphQL\Types\News;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
use App\Models\News\News;
use App\Traits\GraphQL\HasNextPrevLinks;

class NewsType extends BaseType
{
    use HasNextPrevLinks;

    public const NAME = 'News';
    public const MODEL = News::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            $this->getNextPrevLinks(),
            [
                'active' => [
                    'type' => NonNullType::boolean(),
                ],
                'tag' => [
                    'type' => TagType::nonNullType(),
                ],
                'translation' => [
                    'type' => NewsTranslationType::nonNullType(),
                ],
                'translations' => [
                    'type' => NewsTranslationType::nonNullList(),
                ],
                'image' => [
                    'type' => MediaType::type(),
                    'always' => 'id',
                    'alias' => 'media',
                    'resolve' => fn(News $n) => $n->getFirstMedia($n::MEDIA_COLLECTION_NAME),
                ],
                'slug' => [
                    'type' => NonNullType::string()
                ],
            ]
        );
    }
}
