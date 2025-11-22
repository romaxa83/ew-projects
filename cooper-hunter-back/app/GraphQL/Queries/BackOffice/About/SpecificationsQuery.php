<?php

namespace App\GraphQL\Queries\BackOffice\About;

use App\GraphQL\Types\Catalog\Features\Specifications\SpecificationType;
use App\Models\Catalog\Features\Specification;
use App\Permissions\Catalog\Features\Specifications\ListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

class SpecificationsQuery extends BaseQuery
{
    public const NAME = 'specifications';
    public const PERMISSION = ListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            [
                'id' => ['name' => 'id', 'type' => Type::id()],
            ],
            $this->getActiveArgs()
        );
    }

    public function type(): Type
    {
        return SpecificationType::list();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return Specification::query()
            ->filter($args)
            ->latest('sort')
            ->get();
    }
}
