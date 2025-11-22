<?php

namespace App\GraphQL\Queries\FrontOffice\Stores;

use App\GraphQL\Types\Stores\StoreCategoryType;
use App\Models\Stores\StoreCategory;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class OnlineStoresQuery extends BaseQuery
{
    public const NAME = 'onlineStores';

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return StoreCategoryType::nonNullList();
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): mixed
    {
        return StoreCategory::query()
            ->select($fields->getSelect() ?: ['id'])
            ->with($fields->getRelations())
            ->filter($args)
            ->latest('sort')
            ->get();
    }

    protected function initArgs(array $args): array
    {
        $args['active'] = true;

        return $args;
    }
}
