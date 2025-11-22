<?php

namespace App\GraphQL\Subscriptions\Common\Alerts;

use App\GraphQL\Types\Alerts\AlertSubscriptionType;
use Core\WebSocket\GraphQL\Subscriptions\BaseSubscription;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseAlertSubscription extends BaseSubscription
{
    public const NAME = 'alert';

    public function __construct()
    {
        $this->setSubscriptionGuard();
    }

    abstract protected function setSubscriptionGuard(): void;

    public function type(): Type
    {
        return AlertSubscriptionType::nonNullType();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return collect(['id' => $context['alert']]);
    }
}
