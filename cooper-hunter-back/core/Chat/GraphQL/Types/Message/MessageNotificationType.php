<?php

namespace Core\Chat\GraphQL\Types\Message;

use Core\Chat\Models\MessageNotification;
use Core\GraphQL\Types\BaseType;
use GraphQL\Type\Definition\Type;

class MessageNotificationType extends BaseType
{
    public const NAME = 'MessageNotificationType';
    public const MODEL = MessageNotification::class;
    public const DESCRIPTION = 'Some extra info about message';

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'is_seen' => [
                    'type' => Type::nonNull(Type::boolean()),
                ],
                'is_sender' => [
                    'type' => Type::nonNull(Type::boolean()),
                ],
            ],
        );
    }
}
