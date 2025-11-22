<?php

namespace App\GraphQL\InputTypes\Chat;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\LanguageTypeEnum;
use App\GraphQL\Types\NonNullType;

class ChatMenuTranslationInputType extends BaseInputType
{
    public const NAME = 'ChatMenuTranslationInputType';

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
