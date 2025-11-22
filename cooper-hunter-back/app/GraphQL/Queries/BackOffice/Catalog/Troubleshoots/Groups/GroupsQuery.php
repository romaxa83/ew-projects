<?php

namespace App\GraphQL\Queries\BackOffice\Catalog\Troubleshoots\Groups;

use App\GraphQL\Types\Catalog\Troubleshoots\Groups\TroubleshootGroupType;
use App\Models\Catalog\Troubleshoots\Group;
use App\Permissions\Catalog\Troubleshoots\Group\ListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

class GroupsQuery extends BaseQuery
{
    public const NAME = 'troubleshootGroup';
    public const PERMISSION = ListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            [
                'id' => Type::id(),
                'title' => Type::string(),
                'active' => Type::boolean(),
            ]
        );
    }

    public function type(): Type
    {
        return TroubleshootGroupType::list();
    }

    public function doResolve(
        $root,
        array $args,
        $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return $this->getQuery($fields, $args)->get();
    }

    protected function getQuery(SelectFields $fields, array $args): Group|Builder
    {
        return Group::query()
            ->select($fields->getSelect() ?: ['id'])
            ->filter($args)
            ->with($fields->getRelations())
            ->latest('sort');
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['nullable', 'integer'],
            'title' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
