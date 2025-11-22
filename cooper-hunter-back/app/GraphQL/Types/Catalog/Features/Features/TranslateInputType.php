<?php

namespace App\GraphQL\Types\Catalog\Features\Features;

use GraphQL\Type\Definition\Type;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\BaseInputTranslateType;

class TranslateInputType extends BaseInputTranslateType
{
    public const NAME = 'FeatureTranslatesInputType';

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
        ];
    }
}
