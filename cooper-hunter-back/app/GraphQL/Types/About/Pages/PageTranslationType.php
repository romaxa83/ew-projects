<?php

namespace App\GraphQL\Types\About\Pages;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\LanguageTypeEnum;
use App\GraphQL\Types\NonNullType;
use App\Models\About\PageTranslation;

class PageTranslationType extends BaseType
{
    public const NAME = 'PageTranslationType';
    public const MODEL = PageTranslation::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'language' => [
                'type' => LanguageTypeEnum::nonNullType(),
            ],
            'title' => [
                'type' => NonNullType::string(),
            ],
            'description' => [
                'type' => NonNullType::string(),
            ]
        ];
    }
}
