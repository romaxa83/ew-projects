<?php

namespace Core\Chat\GraphQL\Types\Conversation;

use Core\GraphQL\Types\BaseType;
use GraphQL\Type\Definition\Type;

class ConversationUpdatedEventType extends BaseType
{
    public const NAME = 'ConversationUpdatedEventType';

    public function fields(): array
    {
        return [
            'event' => [
                'type' => ConversationUpdatedEventTypeEnumType::nonNullType(),
            ],
            'conversation_id' => [
                'type' => Type::nonNull(Type::id()),
                'description' => 'The ID of the conversation the event belongs to.',
            ],
            'message_id' => [
                'type' => Type::id(),
                'description' => 'May be present if the event is about a message.',
            ],
        ];
    }
}
