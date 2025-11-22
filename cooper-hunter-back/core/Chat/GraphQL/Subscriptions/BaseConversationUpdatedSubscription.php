<?php

namespace Core\Chat\GraphQL\Subscriptions;

use Core\Chat\Entities\Conversations\ConversationUpdatedSubscriptionEntity;
use Core\Chat\GraphQL\Types\Conversation\ConversationUpdatedEventType;
use Core\WebSocket\GraphQL\Subscriptions\BaseSubscription;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseConversationUpdatedSubscription extends BaseSubscription
{
    public const NAME = 'chatConversationUpdated';
    public const DESCRIPTION = 'Send event about any changes in chat: conversation started, user joined/leaved, message sent, etc...';

    public function type(): Type
    {
        return ConversationUpdatedEventType::nonNullType();
    }

    abstract public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ConversationUpdatedSubscriptionEntity;
}
