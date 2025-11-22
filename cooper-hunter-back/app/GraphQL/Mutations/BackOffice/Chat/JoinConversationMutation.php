<?php

namespace App\GraphQL\Mutations\BackOffice\Chat;

use App\Events\Chat\ConversationIsProcessed;
use App\GraphQL\Types\NonNullType;
use Core\Chat\Facades\Chat;
use Core\Chat\GraphQL\Queries\BaseChatQuery;
use Core\Chat\Permissions\ChatMessagingPermission;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Error\AuthorizationError;
use Rebing\GraphQL\Support\SelectFields;

class JoinConversationMutation extends BaseChatQuery
{
    public const NAME = 'chatJoinConversation';
    public const PERMISSION = ChatMessagingPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'conversation_id' => [
                'type' => NonNullType::id(),
            ]
        ];
    }

    public function type(): Type
    {
        return NonNullType::boolean();
    }

    /**
     * @throws AuthorizationError
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        $conversation = Chat::conversations()
            ->findOrFail($args['conversation_id']);

        $isNewConversationProcessed = $conversation->participants()->count() < 2;

        $admin = $this->getUser();

        $admin->joinConversation($conversation);

        if ($isNewConversationProcessed) {
            event(new ConversationIsProcessed($conversation, $admin));
        }

        return true;
    }
}
