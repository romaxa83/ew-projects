<?php

namespace App\GraphQL\Subscriptions\FrontOffice\Chat;

use App\GraphQL\Subscriptions\FrontOffice\FrontOfficeBroadcaster;
use Core\Chat\Entities\Conversations\ConversationUpdatedSubscriptionEntity;
use Core\Chat\GraphQL\Subscriptions\BaseConversationUpdatedSubscription;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;

class ConversationUpdatedSubscription extends BaseConversationUpdatedSubscription
{
    use FrontOfficeBroadcaster;

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ConversationUpdatedSubscriptionEntity {
        return ConversationUpdatedSubscriptionEntity::makeByContext($context);
    }
}
