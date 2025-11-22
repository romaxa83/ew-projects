<?php

namespace App\GraphQL\Queries\FrontOffice\Counters;

use App\Permissions\Projects\ProjectListPermission;
use App\Services\Projects\ProjectService;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;

class ProjectCounterQuery extends BaseMemberCounterQuery
{
    public const NAME = 'projectCounter';
    public const PERMISSION = ProjectListPermission::KEY;

    public function __construct(protected ProjectService $service)
    {
        $this->setMemberGuard();
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): int
    {
        return $this
            ->user()
            ?->projects()
            ->count()
            ?: 0;
    }
}
