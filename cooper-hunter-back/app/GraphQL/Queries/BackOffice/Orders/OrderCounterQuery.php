<?php


namespace App\GraphQL\Queries\BackOffice\Orders;

use App\GraphQL\Types\Orders\OrderCounterType;
use App\Permissions\Orders\OrderListPermission;
use App\Services\Orders\OrderService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Support\SelectFields;

class OrderCounterQuery extends BaseQuery
{
    public const NAME = 'orderCounter';
    public const PERMISSION = OrderListPermission::KEY;

    public function __construct(private OrderService $orderService)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return OrderCounterType::nonNullType();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return $this->orderService->getCounterData($this->user());
    }
}
