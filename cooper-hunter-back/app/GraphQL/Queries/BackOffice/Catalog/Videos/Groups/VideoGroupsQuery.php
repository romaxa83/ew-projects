<?php

namespace App\GraphQL\Queries\BackOffice\Catalog\Videos\Groups;

use App\GraphQL\Types\Catalog\Videos\Groups;
use App\Models\Catalog\Videos\Group;
use App\Permissions\Catalog\Videos\Group\ListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class VideoGroupsQuery extends BaseQuery
{
    public const NAME = 'video';
    public const PERMISSION = ListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            $this->paginationArgs(),
            $this->sortArgs(),
            [
                'id' => Type::id(),
                'title' => Type::string(),
                'active' => Type::boolean(),
            ]
        );
    }

    public function type(): Type
    {
        return Groups\VideoGroupType::paginate();
    }

    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->paginate(
            Group::query()
                ->select($fields->getSelect() ?: ['id'])
                ->filter($args)
                ->with($fields->getRelations())
                ->latest('sort'),
            $args
        );
    }

    protected function rules(array $args = []): array
    {
        return array_merge(
            $this->paginationRules(),
            [
                'id' => ['nullable', 'integer'],
                'title' => ['nullable', 'string'],
                'active' => ['nullable', 'boolean'],
            ]
        );
    }
}




