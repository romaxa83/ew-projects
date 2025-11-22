<?php

namespace App\GraphQL\Types\Catalog\Products;

use App\GraphQL\Types\BaseInputTranslateType;
use App\GraphQL\Types\Enums\LanguageTypeEnum;
use GraphQL\Type\Definition\Type;

class ProductTranslateInputType extends BaseInputTranslateType
{
    public const NAME = 'ProductTranslatesInputType';

    public function fields(): array
    {
        return [
            'language' => [
                'description' => 'Язык (en, es)',
                'type' => LanguageTypeEnum::nonNullType(),
            ],
            'description' => [
                'description' => 'Описание',
                'type' => Type::string(),
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
