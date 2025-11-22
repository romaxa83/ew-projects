<?php

namespace Core\Chat\GraphQL\Queries\Messages;

use Core\Chat\Exceptions\MessageableException;
use Core\Chat\Facades\Chat;
use Core\Chat\GraphQL\Queries\BaseChatQuery;
use Core\Chat\GraphQL\Types\Message\MessageType;
use Core\Chat\Models\Conversation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Error\AuthorizationError;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseMessageQuery extends BaseChatQuery
{
    public const NAME = 'chatMessages';
    public const DESCRIPTION = 'Get a list of paginated messages in conversation';

    public function args(): array
    {
        return array_merge(
            parent::args(),
            [
                'ids' => [
                    'type' => Type::listOf(Type::nonNull(Type::id())),
                    'description' => 'Message ids',
                ],
                'messages_before_id' => [
                    'type' => Type::id(),
                    'description' => 'Get messages older than given id'
                ],
                'conversation_id' => [
                    'type' => Type::nonNull(Type::id()),
                ],
            ]
        );
    }

    public function type(): Type
    {
        return MessageType::paginate();
    }

    /**
     * @throws MessageableException
     * @throws AuthorizationError
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        $conversation = $this->getConversation($args['conversation_id']);

        return $this->transform(
            $this->getMessages($conversation, $args)
        );
    }

    /**
     * @throws AuthorizationError
     */
    protected function getConversation(int $conversationId): Conversation
    {
        return Chat::conversations()
            ->findForUserOrFail($this->getUser(), $conversationId);
    }

    protected function transform(LengthAwarePaginator $messages): LengthAwarePaginator
    {
        $items = $messages->items();

        sort($items);

        return $messages->setCollection(collect($items));
    }

    /**
     * @throws AuthorizationError
     */
    protected function getMessages(Conversation $conversation, array $args): LengthAwarePaginator
    {
        return Chat::messages()
            ->forConversation($conversation)
            ->forParticipant($this->getUser())
            ->filter($args)
            ->paginate($args);
    }
}
