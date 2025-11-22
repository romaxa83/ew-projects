<?php


namespace App\GraphQL\Queries\FrontOffice\Orders;


use App\GraphQL\Types\Orders\OrderTotalType;
use App\Permissions\Orders\OrderListPermission;
use App\Services\Orders\OrderService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Support\SelectFields;

class OrderTotalQuery extends BaseQuery
{

    public const NAME = 'orderTotal';
    public const PERMISSION = OrderListPermission::KEY;

    public function __construct(protected OrderService $orderService)
    {
        $this->setTechnicianGuard();
    }

    /**
     * @return array[]
     */
    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return OrderTotalType::nonNullType();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return $this->orderService->getTotalData($this->user());
    }
}
