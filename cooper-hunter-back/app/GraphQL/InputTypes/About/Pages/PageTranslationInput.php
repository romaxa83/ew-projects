<?php

namespace App\GraphQL\InputTypes\About\Pages;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\LanguageTypeEnum;
use App\GraphQL\Types\NonNullType;

class PageTranslationInput extends BaseInputType
{
    public const NAME = 'PageTranslationInput';

    public function fields(): array
    {
        return [
            'language' => [
                'type' => LanguageTypeEnum::nonNullType(),
            ],
            'title' => [
                'type' => NonNullType::string(),
            ],
            'description' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
