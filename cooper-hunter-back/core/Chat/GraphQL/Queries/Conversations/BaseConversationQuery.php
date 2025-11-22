<?php

namespace Core\Chat\GraphQL\Queries\Conversations;

use Core\Chat\Exceptions\MessageableException;
use Core\Chat\Facades\Chat;
use Core\Chat\GraphQL\Queries\BaseChatQuery;
use Core\Chat\GraphQL\Types\Conversation\ConversationType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Error\AuthorizationError;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseConversationQuery extends BaseChatQuery
{
    public const NAME = 'chatConversations';
    public const DESCRIPTION = 'Get list of all user\'s conversations';

    public function type(): Type
    {
        return ConversationType::paginate();
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
        return Chat::conversations()
            ->paginateForUser(
                $this->getUser()
            );
    }
}
