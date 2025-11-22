<?php

namespace App\GraphQL\Subscriptions\FrontOffice\Members;

use App\GraphQL\Subscriptions\FrontOffice\FrontOfficeBroadcaster;
use App\GraphQL\Types\Members\MemberSubscriptionType;
use Closure;
use Core\WebSocket\GraphQL\Subscriptions\BaseSubscription;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Support\SelectFields;

class MemberSubscription extends BaseSubscription
{
    use FrontOfficeBroadcaster;

    public const NAME = 'member';

    public function __construct()
    {
        $this->setMemberGuard();
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
        return MemberSubscriptionType::nonNullType();
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
                'id' => $context['member'],
                'type' => $context['type']
            ]
        );
    }
}
