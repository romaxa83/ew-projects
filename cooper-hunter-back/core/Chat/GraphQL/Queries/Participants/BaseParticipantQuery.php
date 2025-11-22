<?php

namespace Core\Chat\GraphQL\Queries\Participants;

use Core\Chat\Exceptions\MessageableException;
use Core\Chat\Facades\Chat;
use Core\Chat\GraphQL\Queries\BaseChatQuery;
use Core\Chat\GraphQL\Types\Participation\ParticipationType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Error\AuthorizationError;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseParticipantQuery extends BaseChatQuery
{
    public const NAME = 'chatParticipants';
    public const DESCRIPTION = 'Get a list of all participants in conversation';

    public function args(): array
    {
        return [
            'conversation_id' => [
                'type' => Type::nonNull(Type::id()),
            ],
        ];
    }

    public function type(): Type
    {
        return ParticipationType::nonNullList();
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
    ): Collection {
        return Chat::conversations()
            ->findForUserOrFail($this->getUser(), $args['conversation_id'])
            ->getParticipants();
    }
}
