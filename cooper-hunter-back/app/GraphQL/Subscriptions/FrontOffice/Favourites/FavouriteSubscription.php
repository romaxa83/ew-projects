<?php

namespace App\GraphQL\Subscriptions\FrontOffice\Favourites;

use App\GraphQL\Subscriptions\FrontOffice\FrontOfficeBroadcaster;
use App\GraphQL\Types\Favourites\FavouriteSubscriptionType;
use Closure;
use Core\WebSocket\GraphQL\Subscriptions\BaseSubscription;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Support\SelectFields;

class FavouriteSubscription extends BaseSubscription
{
    use FrontOfficeBroadcaster;

    public const NAME = 'favourite';

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
        return FavouriteSubscriptionType::nonNullType();
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
                'favourite_id' => $context['favourite_id'],
                'favourite_type' => $context['favourite_type'],
                'action' => $context['action'],
            ]
        );
    }
}
