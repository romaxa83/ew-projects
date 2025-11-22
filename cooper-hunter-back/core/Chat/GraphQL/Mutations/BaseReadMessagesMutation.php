<?php

namespace Core\Chat\GraphQL\Mutations;

use Core\Chat\Facades\Chat;
use Core\Chat\GraphQL\Queries\BaseChatQuery;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Error\AuthorizationError;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseReadMessagesMutation extends BaseChatQuery
{
    public const NAME = 'chatReadMessages';
    public const DESCRIPTION = 'Returns the number of messages marked as read';

    public function args(): array
    {
        return [
            'conversation_id' => [
                'type' => Type::nonNull(Type::id()),
            ],
            'message_ids' => [
                'type' => Type::listOf(Type::nonNull(Type::id())),
            ],
        ];
    }

    public function type(): Type
    {
        return NonNull::int();
    }

    /**
     * @throws AuthorizationError
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): int
    {
        $conversation = Chat::conversations()
            ->findForUserOrFail($participant = $this->getUser(), $args['conversation_id']);

        return Chat::messages()
            ->forConversation($conversation)
            ->forParticipant($participant)
            ->markAsRead($args['message_ids']);
    }
}
