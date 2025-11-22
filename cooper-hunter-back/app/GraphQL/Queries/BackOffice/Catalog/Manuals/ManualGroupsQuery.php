<?php

namespace App\GraphQL\Queries\BackOffice\Catalog\Manuals;

use App\GraphQL\Types\Catalog\Manuals\ManualGroupType;
use App\Models\Catalog\Manuals\ManualGroup;
use App\Permissions\Catalog\Manuals\ManualListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class ManualGroupsQuery extends BaseQuery
{
    public const NAME = 'manualGroups';
    public const PERMISSION = ManualListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return ManualGroupType::paginate();
    }

    public function args(): array
    {
        return array_merge(
            parent::args(),
            [
                'id' => [
                    'name' => 'id',
                    'type' => Type::id()
                ],
                'query' => [
                    'type' => Type::string(),
                    'description' => 'Filter by title.'
                ]
            ]
        );
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return
            ManualGroup::query()
                ->filter($args)
                ->latest('sort')
                ->paginate(perPage: $args['per_page'], page: $args['page']);
    }
}
