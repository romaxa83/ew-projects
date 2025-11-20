<?php

namespace App\GraphQL\Queries\BackOffice\Calls\Queue;

use App\GraphQL\Types\Calls\Queue\QueueType;
use App\GraphQL\Types\Enums\Calls\QueueStatusEnum;
use App\Permissions;
use App\Repositories\Calls\QueueRepository;
use Closure;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class QueuesQuery extends BaseQuery
{
    public const NAME = 'CallQueues';
    public const PERMISSION = Permissions\Calls\Queue\ListPermission::KEY;

    public function __construct(
        protected QueueRepository $repo
    )
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
        return array_merge(
            $this->paginationArgs(),
            [
                'id' => Type::id(),
                'department_id' => Type::id(),
                'serial_number' => Type::string(),
                'case_id' => Type::string(),
                'search' => Type::string(),
                'statuses' => Type::listOf(
                    QueueStatusEnum::type()
                ),
            ],
        );
    }

    public function type(): Type
    {
        return QueueType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator
    {
        return $this->repo->getPagination(
            filters: $args
        );
    }
}
