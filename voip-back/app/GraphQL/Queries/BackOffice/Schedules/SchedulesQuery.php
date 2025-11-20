<?php

namespace App\GraphQL\Queries\BackOffice\Schedules;

use App\GraphQL\Types\Schedules\ScheduleType;
use App\Repositories\Schedules\ScheduleRepository;
use Closure;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;
use App\Permissions;

class SchedulesQuery extends BaseQuery
{
    public const NAME = 'Schedules';
    public const PERMISSION = Permissions\Schedules\ListPermission::KEY;

    public function __construct(protected ScheduleRepository $repo)
    {}

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool
    {
        $this->setAuthGuard();

        return parent::authorize($root, $args, $ctx, $info, $fields);
    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return ScheduleType::list();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection
    {
        return $this->repo->getCollection(
            relation: ['days', 'additionalDays']
        );
    }
}

