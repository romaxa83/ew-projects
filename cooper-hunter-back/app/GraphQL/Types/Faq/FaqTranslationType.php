<?php

namespace App\GraphQL\Types\Faq;

use App\GraphQL\Types\BaseTranslationType;
use App\GraphQL\Types\NonNullType;
use App\Models\Faq\FaqTranslation;
use GraphQL\Type\Definition\Type;

class FaqTranslationType extends BaseTranslationType
{
    public const NAME = 'FaqTranslationType';
    public const MODEL = FaqTranslation::class;

    public function fields(): array
    {
        return parent::fields() + [
                'question' => [
                    'type' => NonNullType::string(),
                ],
                'answer' => [
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
