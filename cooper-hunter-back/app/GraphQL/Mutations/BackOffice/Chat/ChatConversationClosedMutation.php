<?php


namespace App\GraphQL\Mutations\BackOffice\Chat;


use App\GraphQL\Types\NonNullType;
use Core\Chat\Facades\Chat;
use Core\Chat\Models\Conversation;
use Core\Chat\Permissions\ChatListPermission;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ChatConversationClosedMutation extends BaseMutation
{
    public const NAME = 'chatCloseConversation';
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
        return makeTransaction(
            static fn() => Chat::conversation(Conversation::find($args['id']))
                ->close()
        );
    }
}
