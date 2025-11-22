<?php


namespace App\GraphQL\Queries\Common\SupportRequests;


use App\GraphQL\Types\SupportRequests\SupportRequestCounterType;
use App\Permissions\SupportRequests\SupportRequestListPermission;
use App\Services\SupportRequests\SupportRequestService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseSupportRequestCounterQuery extends BaseQuery
{
    public const NAME = 'supportRequestCounter';
    public const PERMISSION = SupportRequestListPermission::KEY;

    public function __construct(private SupportRequestService $service)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function type(): Type
    {
        return SupportRequestCounterType::nonNullType();
    }

    public function args(): array
    {
        return [];
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return $this->service->getCounter($this->user());
    }
}
