<?php

namespace App\GraphQL\Types\Catalog\Tickets;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\LanguageTypeEnum;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Tickets\TicketTranslation;

class TicketTranslationType extends BaseType
{
    public const NAME = 'TicketTranslationType';
    public const MODEL = TicketTranslation::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id()
            ],
            'title' => [
                'type' => NonNullType::string(),
                'description' => 'Issue title.',
            ],
            'description' => [
                'type' => NonNullType::string(),
                'description' => 'Issue description.',
            ],
            'language' => [
                'type' => LanguageTypeEnum::nonNullType(),
            ]
        ];
    }
}
