<?php

namespace App\GraphQL\Types\Commercial\Commissioning;

use App\GraphQL\Types\BaseInputTranslateType;
use App\GraphQL\Types\Enums\LanguageTypeEnum;
use GraphQL\Type\Definition\Type;

class ProtocolTranslationInputType extends BaseInputTranslateType
{
    public const NAME = 'CommissioningProtocolTranslationInputType';

    public function fields(): array
    {
        return [
            'language' => [
                'description' => 'Язык (en, es)',
                'type' => LanguageTypeEnum::nonNullType(),
            ],
            'title' => [
                'description' => 'Title',
                'type' => Type::string(),
            ],
            'description' => [
                'description' => 'Description',
                'type' => Type::string(),
            ],
        ];
    }
}
