<?php

namespace App\GraphQL\Queries\FrontOffice\Dealers;

use App\GraphQL\Types\Dealers\DealerProfileType;
use App\Models\Dealers\Dealer;
use Closure;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class DealerProfileQuery extends BaseQuery
{
    public const NAME = 'dealerProfile';

    public function __construct()
    {
        $this->setDealerGuard();
    }

    public function authorize($root, array $args, $ctx, ResolveInfo $info = null, Closure $fields = null): bool
    {
        return $this->authCheck();
    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return DealerProfileType::type();
    }

    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Dealer
    {
        return $this->user()
            ->load($fields->getRelations());
    }
}
