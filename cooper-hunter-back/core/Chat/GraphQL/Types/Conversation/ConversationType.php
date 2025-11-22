<?php

namespace Core\Chat\GraphQL\Types\Conversation;

use Core\Chat\GraphQL\Types\Message\LastMessageType;
use Core\Chat\Models\Conversation;
use Core\Contracts\HasAvatar;
use Core\GraphQL\Types\BaseType;
use Core\GraphQL\Types\MediaType;
use GraphQL\Type\Definition\Type;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ConversationType extends BaseType
{
    public const NAME = 'ConversationType';
    public const MODEL = Conversation::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'direct_message' => [
                    'type' => Type::nonNull(Type::boolean()),
                    'description' => 'Determine if conversation is between two participants',
                ],
                'title' => [
                    'type' => Type::nonNull(Type::string()),
                ],
                'description' => [
                    'type' => Type::string(),
                ],
                'last_message' => [
                    'type' => LastMessageType::type(),
                    'alias' => 'lastMessage',
                ],
                'unread_messages_count' => [
                    'type' => Type::nonNull(Type::int()),
                    'resolve' => static fn(Conversation $c) => $c->unread_messages_count ?: 0,
                    'selectable' => false,
                ],
                'avatar' => [
                    'type' => MediaType::type(),
                    'always' => 'id',
                    'alias' => 'media',
                ],
                'is_closed' => [
                    'type' => Type::nonNull(Type::boolean())
                ],
                'can_messaging' => [
                    'type' => Type::nonNull(Type::boolean())
                ],
            ]
        );
    }

    protected function resolveAvatarField(Conversation $c): ?Media
    {
        if ($c instanceof HasAvatar) {
            return $c->avatar();
        }

        return $c->getFirstMedia($c::MEDIA_COLLECTION_NAME);
    }
}
