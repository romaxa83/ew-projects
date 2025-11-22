<?php

namespace App\GraphQL\InputTypes\Menu;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\LanguageTypeEnum;
use App\GraphQL\Types\NonNullType;

class MenuTranslationInput extends BaseInputType
{
    public const NAME = 'MenuTranslationInput';

    public function fields(): array
    {
        return [
            'language' => [
                'description' => 'Язык (en, es)',
                'type' => LanguageTypeEnum::nonNullType(),
            ],
            'title' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
