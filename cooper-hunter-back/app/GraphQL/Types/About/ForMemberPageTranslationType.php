<?php

namespace App\GraphQL\Types\About;

use App\GraphQL\Types\BaseTranslationType;
use App\GraphQL\Types\NonNullType;
use App\Models\About\ForMemberPageTranslation;
use GraphQL\Type\Definition\Type;

class ForMemberPageTranslationType extends BaseTranslationType
{
    public const NAME = 'ForMemberPageTranslationType';
    public const MODEL = ForMemberPageTranslation::class;

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
