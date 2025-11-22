<?php

namespace App\GraphQL\Queries\BackOffice\Chat\Conversations;

use App\Enums\Chat\ConversationTabEnum;
use App\GraphQL\Types\Enums\Chat\ConversationTabEnumType;
use App\Repositories\Chat\Conversations\ConversationRepository;
use Core\Chat\GraphQL\Queries\BaseChatQuery;
use Core\Chat\GraphQL\Types\Conversation\ConversationType;
use Core\Chat\Permissions\ChatListPermission;
use Core\Exceptions\TranslatedException;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class ConversationQuery extends BaseChatQuery
{
    public const NAME = 'chatConversations';
    public const DESCRIPTION = 'Get tabbed list of conversations';
    public const PERMISSION = ChatListPermission::KEY;

    public function __construct(protected ConversationRepository $repository)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            [
                'tab' => [
                    'type' => ConversationTabEnumType::nonNullType(),
                ],
                'query' => [
                    'type' => Type::string(),
                    'description' => 'Search by technician name',
                ],
            ],
            parent::args(),
        );
    }

    public function type(): Type
    {
        return ConversationType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        $repo = $this->repository->search(
            data_get($args, 'query')
        );

        return match ($args['tab']) {
            ConversationTabEnum::ALL => $repo->all($this->user()),
            ConversationTabEnum::MY => $repo->my($this->user()),
            ConversationTabEnum::NEW => $repo->new(),
            default => throw new TranslatedException('Conversation Tab is not allowed.'),
        };
    }
}
