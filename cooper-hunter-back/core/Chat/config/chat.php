<?php

use Core\Chat\Models\Conversation;
use Core\Chat\Models\Message;
use Core\Chat\Models\MessageNotification;
use Core\Chat\Models\Participation;

return [
    /*
     * Chat models
     *
     * Must be instanced of \Core\Chat\Models\BaseChatModel
     */
    'models' => [
        'conversation' => Conversation::class,
        'participation' => Participation::class,
        'message' => Message::class,
        'message_notification' => MessageNotification::class,
    ],

    'graphql' => [
        'types' => [
            Core\Chat\GraphQL\Types\Conversation\ConversationType::class,
            Core\Chat\GraphQL\Types\Conversation\ConversationUpdatedEventTypeEnumType::class,
            Core\Chat\GraphQL\Types\Conversation\ConversationUpdatedEventType::class,

            Core\Chat\GraphQL\Types\Participation\ParticipationType::class,
            Core\Chat\GraphQL\Types\Participation\ParticipantType::class,

            Core\Chat\GraphQL\Types\Message\MessageTypeEnumType::class,
            Core\Chat\GraphQL\Types\Message\MessageType::class,
            Core\Chat\GraphQL\Types\Message\LastMessageType::class,
            Core\Chat\GraphQL\Types\Message\MessageNotificationType::class,
            Core\Chat\GraphQL\Types\Message\MessageMetaType::class,
        ],
    ],

    'permissions' => [
        'group' => Core\Chat\Permissions\ChatPermissionGroup::class,
        'grants' => [
            Core\Chat\Permissions\ChatListPermission::class,
            Core\Chat\Permissions\ChatMessagingPermission::class,
        ]
    ],
];
