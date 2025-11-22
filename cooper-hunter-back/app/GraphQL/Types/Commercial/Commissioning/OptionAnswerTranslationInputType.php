<?php

namespace App\GraphQL\Types\Commercial\Commissioning;

use App\GraphQL\Types\BaseInputTranslateType;
use App\GraphQL\Types\Enums\LanguageTypeEnum;
use GraphQL\Type\Definition\Type;

class OptionAnswerTranslationInputType extends BaseInputTranslateType
{
    public const NAME = 'CommissioningOptionAnswerTranslationInputType';

    public function fields(): array
    {
        return [
            'language' => [
                'description' => 'Язык (en, es)',
                'type' => LanguageTypeEnum::nonNullType(),
            ],
            'text' => [
                'description' => 'Text',
                'type' => Type::string(),
            ],
        ];
    }
}

