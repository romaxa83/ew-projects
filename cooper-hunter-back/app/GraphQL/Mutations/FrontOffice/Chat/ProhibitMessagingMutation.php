<?php

namespace App\GraphQL\Mutations\FrontOffice\Chat;

use App\Events\Chat\ConversationIsProcessed;
use App\GraphQL\Types\NonNullType;
use Core\Chat\Facades\Chat;
use Core\Chat\GraphQL\Queries\BaseChatQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ProhibitMessagingMutation extends BaseChatQuery
{
    public const NAME = 'prohibitMessaging';

    public function __construct()
    {
        $this->setTechnicianGuard();
    }

    public function args(): array
    {
        return [];
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
        $conversation = Chat::conversations()
            ->getQueryForUser(
                $technician = $this->getUser()
            )->first();

        if (!$conversation) {
            return true;
        }

        $conversation->can_messaging = false;
        $conversation->save();

        event(new ConversationIsProcessed($conversation, $technician));

        return true;
    }
}