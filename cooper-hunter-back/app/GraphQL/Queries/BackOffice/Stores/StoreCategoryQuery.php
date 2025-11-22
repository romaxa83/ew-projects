<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\BackOffice\Stores;

use App\GraphQL\Types\Stores\StoreCategoryType;
use App\Models\Stores\StoreCategory;
use App\Permissions\Stores\StoreCategories\StoreCategoryListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

class StoreCategoryQuery extends BaseQuery
{
    public const NAME = 'storeCategory';
    public const PERMISSION = StoreCategoryListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            $this->getIdArgs(),
            $this->getActiveArgs(),
        );
    }

    public function type(): Type
    {
        return StoreCategoryType::nonNullList();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return StoreCategory::query()
            ->select($fields->getSelect() ?: ['id'])
            ->with($fields->getRelations())
            ->filter($args)
            ->get();
    }
}
