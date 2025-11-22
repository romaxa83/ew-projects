<?php

namespace App\GraphQL\Types\Chat;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\LanguageTypeEnum;
use App\GraphQL\Types\NonNullType;
use App\Models\Chat\ChatMenuTranslation;

class ChatMenuTranslationType extends BaseType
{
    public const NAME = 'ChatMenuTranslationType';
    public const MODEL = ChatMenuTranslation::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'title' => [
                'type' => NonNullType::string()
            ],
            'language' => [
                'type' => LanguageTypeEnum::nonNullType()
            ]
        ];
    }
}
