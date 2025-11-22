<?php

namespace App\GraphQL\Types\Chat;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Chat\ConversationTabEnumType;
use App\GraphQL\Types\NonNullType;

class ChatInTabCounterType extends BaseType
{
    public const NAME = 'ChatInTabCounterType';

    public function fields(): array
    {
        return [
            'tab' => [
                'type' => ConversationTabEnumType::nonNullType(),
            ],
            'count' => [
                'type' => NonNullType::int(),
            ],
        ];
    }
}
