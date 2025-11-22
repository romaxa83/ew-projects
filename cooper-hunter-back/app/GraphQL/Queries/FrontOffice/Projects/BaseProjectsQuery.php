<?php

namespace App\GraphQL\Queries\FrontOffice\Projects;

use App\GraphQL\Types\Projects\ProjectType;
use App\Permissions\Projects\ProjectListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseProjectsQuery extends BaseQuery
{
    public const PERMISSION = ProjectListPermission::KEY;

    public function __construct()
    {
        $this->setMemberGuard();
    }

    public function type(): Type
    {
        return ProjectType::paginate();
    }

    public function args(): array
    {
        return array_merge(
            [
                'id' => [
                    'type' => Type::id()
                ]
            ],
            parent::args()
        );
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->paginate(
            $this->user()
                ->projects()
                ->filter($args)
                ->select($fields->getSelect() ?: ['id'])
                ->with($fields->getRelations())
                ->latest(),
            $args,
        );
    }
}
