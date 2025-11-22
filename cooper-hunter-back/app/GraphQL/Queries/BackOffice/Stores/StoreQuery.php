<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\BackOffice\Stores;

use App\GraphQL\Types\Stores\StoreType;
use App\Models\Stores\Store;
use App\Models\Stores\StoreCategory;
use App\Permissions\Stores\Stores\StoreListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class StoreQuery extends BaseQuery
{
    public const NAME = 'store';
    public const PERMISSION = StoreListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            [
                'store_category_id' => [
                    'type' => Type::id(),
                    'rules' => ['nullable', Rule::exists(StoreCategory::class, 'id')],
                ],
            ],
            $this->getIdArgs(),
            $this->getActiveArgs(),
        );
    }

    public function type(): Type
    {
        return StoreType::nonNullList();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return Store::query()
            ->select($fields->getSelect() ?: ['id'])
            ->with($fields->getRelations())
            ->filter($args)
            ->get();
    }
}
