<?php

namespace App\GraphQL\Mutations\BackOffice\Chat;

use App\Events\Chat\ConversationIsProcessed;
use App\GraphQL\Types\NonNullType;
use App\Models\Chat\Conversation;
use Core\Chat\GraphQL\Queries\BaseChatQuery;
use Core\Chat\Permissions\ChatListPermission;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ChatConversationStartMutation extends BaseChatQuery
{
    public const NAME = 'chatStartConversation';
    public const PERMISSION = ChatListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Conversation::class, 'id')
                ]
            ]
        ];
    }

    public function type(): Type
    {
        return NonNullType::boolean();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return bool
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        $conversation = Conversation::findOrFail($args['id']);
        $conversation->can_messaging = true;
        $conversation->timestamps = false;

        $conversation->save();

        event(new ConversationIsProcessed($conversation, $this->getUser()));

        return true;
    }
}
