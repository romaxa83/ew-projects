<?php

namespace App\GraphQL\InputTypes\Catalog\Labels;

use App\GraphQL\Types\BaseInputTranslateType;
use App\GraphQL\Types\Enums\LanguageTypeEnum;
use App\GraphQL\Types\NonNullType;

class LabelTranslationInputType extends BaseInputTranslateType
{
    public const NAME = 'LabelTranslationInputType';

    public function fields(): array
    {
        return [
            'language' => [
                'description' => 'Язык (en, es)',
                'type' => LanguageTypeEnum::nonNullType(),
            ],
            'title' => [
                'description' => 'Title',
                'type' => NonNullType::string(),
            ],
        ];
    }
}
