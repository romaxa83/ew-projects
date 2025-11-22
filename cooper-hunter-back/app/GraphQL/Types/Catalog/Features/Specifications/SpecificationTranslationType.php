<?php

namespace App\GraphQL\Types\Catalog\Features\Specifications;

use App\GraphQL\Types\BaseTranslationType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Features\SpecificationTranslation;
use GraphQL\Type\Definition\Type;

class SpecificationTranslationType extends BaseTranslationType
{
    public const NAME = 'SpecificationTranslationType';
    public const MODEL = SpecificationTranslation::class;

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
