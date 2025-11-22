<?php

namespace App\GraphQL\Types\Catalog\Categories;

use App\GraphQL\Types\BaseInputTranslateType;
use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;

class CategoryTranslateInputType extends BaseInputTranslateType
{
    public const NAME = 'CategoryTranslatesInputType';

    public function fields(): array
    {
        return [
            'language' => [
                'description' => 'Язык (en, es)',
                'type' => NonNullType::string(),
                'rules' => ['max:3']
            ],
            'title' => [
                'description' => 'Название',
                'type' => NonNullType::string(),
                'rules' => ['max:250']
            ],
            'description' => [
                'description' => 'Описание',
                'type' => Type::string(),
                'rules' => ['nullable']
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
