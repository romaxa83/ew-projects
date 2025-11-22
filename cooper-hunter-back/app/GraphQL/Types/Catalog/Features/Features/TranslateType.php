<?php

namespace App\GraphQL\Types\Catalog\Features\Features;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Features\FeatureTranslation;
use GraphQL\Type\Definition\Type;

class TranslateType extends BaseType
{
    public const NAME = 'FeatureTranslateType';
    public const MODEL = FeatureTranslation::class;

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
            'seo_title' => [
                'type' => Type::string(),
            ],
            'seo_description' => [
                'type' => Type::string(),
            ],
            'seo_h1' => [
                'type' => Type::string(),
            ],
        ];
    }
}

