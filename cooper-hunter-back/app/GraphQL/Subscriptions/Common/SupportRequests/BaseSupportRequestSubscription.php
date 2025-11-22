<?php

namespace App\GraphQL\Subscriptions\Common\SupportRequests;

use App\GraphQL\Types\SupportRequests\SupportRequestSubscriptionType;
use App\Permissions\SupportRequests\SupportRequestListPermission;
use Core\WebSocket\GraphQL\Subscriptions\BaseSubscription;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseSupportRequestSubscription extends BaseSubscription
{
    public const NAME = 'supportRequest';
    public const PERMISSION = SupportRequestListPermission::KEY;

    public function __construct()
    {
        $this->setSubscriptionGuard();
    }

    abstract protected function setSubscriptionGuard(): void;

    public function type(): Type
    {
        return SupportRequestSubscriptionType::nonNullType();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return collect(
            [
                'id' => $context['support_request'],
                'action' => $context['action']
            ]
        );
    }
}
