<?php

namespace App\GraphQL\Subscriptions\Common\Orders;

use App\GraphQL\Types\Orders\OrderSubscriptionType;
use App\Permissions\Orders\OrderListPermission;
use Core\WebSocket\GraphQL\Subscriptions\BaseSubscription;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseOrderSubscription extends BaseSubscription
{
    public const NAME = 'order';
    public const PERMISSION = OrderListPermission::KEY;

    public function __construct()
    {
        $this->setSubscriptionGuard();
    }

    abstract protected function setSubscriptionGuard(): void;

    public function type(): Type
    {
        return OrderSubscriptionType::nonNullType();
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
                'id' => $context['id'],
                'action' => $context['action']
            ]
        );
    }
}
