<?php

namespace App\GraphQL\Mutations\BackOffice\Chat;

use App\Events\Chat\ConversationIsProcessed;
use App\GraphQL\Types\NonNullType;
use Core\Chat\Facades\Chat;
use Core\Chat\GraphQL\Mutations\BaseSendMessageMutation;
use Core\Chat\Permissions\ChatMessagingPermission;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Error\AuthorizationError;
use Rebing\GraphQL\Support\SelectFields;

class SendMessageMutation extends BaseSendMessageMutation
{
    public const PERMISSION = ChatMessagingPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            [
                'conversation_id' => [
                    'type' => NonNullType::id(),
                ]
            ],
            parent::args(),
        );
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

        $this->sendMessage($args, $admin, $conversation);

        return true;
    }
}
