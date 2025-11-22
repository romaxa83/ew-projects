<?php

namespace App\GraphQL\Types\About\Pages;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\About\Page;

class PageType extends BaseType
{
    public const NAME = 'PageType';
    public const MODEL = Page::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'slug' => [
                'type' => NonNullType::string(),
            ],
            'active' => [
                'type' => NonNullType::boolean(),
            ],
            'translation' => [
                'type' => PageTranslationType::nonNullType(),
            ],
            'translations' => [
                'type' => PageTranslationType::nonNullList(),
            ],
            'created_at' => [
                'type' => NonNullType::int(),
                'description' => 'Unix time.',
                'resolve' => fn(Page $page) => $page->created_at->getTimestamp(),
            ],
            'updated_at' => [
                'type' => NonNullType::int(),
                'description' => 'Unix time.',
                'resolve' => fn(Page $page) => $page->updated_at->getTimestamp(),
            ]
        ];
    }
}
