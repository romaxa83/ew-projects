<?php

namespace App\GraphQL\Types\About;

use App\GraphQL\Types\BaseTranslationType;
use App\GraphQL\Types\NonNullType;
use App\Models\About\AboutCompanyTranslation;
use GraphQL\Type\Definition\Type;

class AboutCompanyTranslationType extends BaseTranslationType
{
    public const NAME = 'AboutCompanyTranslationType';
    public const MODEL = AboutCompanyTranslation::class;

    public function fields(): array
    {
        return parent::fields() + [
                'video_link' => [
                    'type' => NonNullType::string(),
                ],
                'title' => [
                    'type' => NonNullType::string(),
                ],
                'description' => [
                    'type' => NonNullType::string(),
                ],
                'short_description' => [
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
                'additional_title' => [
                    'type' => Type::string(),
                ],
                'additional_description' => [
                    'type' => Type::string(),
                ],
                'additional_video_link' => [
                    'type' => Type::string(),
                ],
            ];
    }
}
