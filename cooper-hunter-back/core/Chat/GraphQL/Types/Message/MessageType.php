<?php

namespace Core\Chat\GraphQL\Types\Message;

use Core\Chat\Enums\MessageTypeEnum;
use Core\Chat\GraphQL\Types\Participation\ParticipationType;
use Core\Chat\Models\Message;
use Core\Chat\Models\MessageNotification;
use Core\GraphQL\Types\BaseType;
use Core\GraphQL\Types\MediaType;
use GraphQL\Type\Definition\Type;

class MessageType extends BaseType
{
    public const NAME = 'MessageType';
    public const MODEL = Message::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'body' => [
                    'type' => Type::string(),
                    'description' => 'Message text. Can be empty if message is an attachment',
                ],
                'type' => [
                    'type' => MessageTypeEnumType::nonNullType(),
                ],
                'attachments' => [
                    'description' => 'Message meta always present if message type is :"' . MessageTypeEnum::ATTACHMENT . '"',
                    'type' => MediaType::list(),
                    'always' => 'id',
                    'alias' => 'media',
                ],
                'participation' => [
                    'type' => ParticipationType::type(),
                ],
                'message_info' => [
                    //Message info should be loaded manually, as it indicates information about logged-in user
                    'type' => MessageNotificationType::type(),
                    'is_relation' => false,
                    'selectable' => false,
                    'resolve' => static function (Message $m): ?MessageNotification {
                        if (!$m->relationLoaded('messageNotifications')) {
                            return null;
                        }

                        return $m->messageNotifications->first();
                    }
                ],
            ]
        );
    }
}
