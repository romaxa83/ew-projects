<?php

namespace App\GraphQL\Queries\FrontOffice\Counters;

use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;

class FavouriteCounterQuery extends BaseMemberCounterQuery
{
    public const NAME = 'favouriteCounter';

    public function __construct()
    {
        $this->setMemberGuard();
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): int
    {
        return $this
            ->user()
            ?->favourites()
            ->count()
            ?: 0;
    }
}
