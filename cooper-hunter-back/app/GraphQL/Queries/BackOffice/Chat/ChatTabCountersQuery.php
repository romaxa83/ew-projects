<?php

namespace App\GraphQL\Queries\BackOffice\Chat;

use App\Enums\Chat\ConversationTabEnum;
use App\GraphQL\Types\Chat\ChatInTabCounterType;
use App\Repositories\Chat\Conversations\ConversationRepository;
use Closure;
use Core\Chat\Facades\Chat;
use Core\Chat\GraphQL\Queries\BaseChatQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Error\AuthorizationError;
use Rebing\GraphQL\Support\SelectFields;

class ChatTabCountersQuery extends BaseChatQuery
{
    public const NAME = 'chatTabCounters';

    public function __construct(protected ConversationRepository $repository)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [];
    }

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool {
        return $this->authCheck();
    }

    public function type(): Type
    {
        return ChatInTabCounterType::nonNullList();
    }

    /**
     * @throws AuthorizationError
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): array
    {
        return [
            [
                'tab' => ConversationTabEnum::ALL,
                'count' => 0,
            ],
            [
                'tab' => ConversationTabEnum::NEW,
                'count' => $this->repository->newCount(),
            ],
            [
                'tab' => ConversationTabEnum::MY,
                'count' => Chat::conversations()
                    ->getUnreadCount(
                        $this->getUser()
                    ),
            ],
        ];
    }
}
