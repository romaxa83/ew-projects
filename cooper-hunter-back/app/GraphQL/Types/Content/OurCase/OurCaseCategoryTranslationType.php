<?php

namespace App\GraphQL\Types\Content\OurCase;

use App\GraphQL\Types\BaseTranslationType;
use App\GraphQL\Types\NonNullType;
use App\Models\Content\OurCases\OurCaseCategoryTranslation;
use GraphQL\Type\Definition\Type;

class OurCaseCategoryTranslationType extends BaseTranslationType
{
    public const NAME = 'OurCaseCategoryTranslationType';
    public const MODEL = OurCaseCategoryTranslation::class;

    public function fields(): array
    {
        return parent::fields() + [
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
            ];
    }
}
