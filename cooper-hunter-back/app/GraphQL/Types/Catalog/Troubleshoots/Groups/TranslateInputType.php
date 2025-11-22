<?php

namespace App\GraphQL\Types\Catalog\Troubleshoots\Groups;

use App\GraphQL\Types\BaseInputTranslateType;
use App\GraphQL\Types\Enums\LanguageTypeEnum;
use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;

class TranslateInputType extends BaseInputTranslateType
{
    public const NAME = 'TroubleshootGroupTranslatesInputType';

    public function fields(): array
    {
        return [
            'language' => [
                'description' => 'Язык (en, es)',
                'type' => LanguageTypeEnum::nonNullType(),
            ],
            'title' => [
                'description' => 'Название',
                'type' => NonNullType::string(),
                'rules' => ['max:250']
            ],
            'description' => [
                'description' => 'Описание',
                'type' => Type::string(),
            ],
        ];
    }
}
