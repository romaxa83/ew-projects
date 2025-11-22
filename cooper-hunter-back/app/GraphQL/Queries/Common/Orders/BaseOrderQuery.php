<?php

namespace App\GraphQL\Queries\Common\Orders;

use App\GraphQL\Types\Enums\Orders\OrderFilterCostStatusTypeEnum;
use App\GraphQL\Types\Enums\Orders\OrderStatusTypeEnum;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Orders\OrderType;
use App\Permissions\Orders\OrderListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseOrderQuery extends BaseQuery
{

    public const NAME = 'order';
    public const PERMISSION = OrderListPermission::KEY;

    /**
     * @return array[]
     */
    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::id(),
            ],
            'cost_status' => [
                'type' => OrderFilterCostStatusTypeEnum::list(),
                'description' => 'Filter by order cost status',
            ],
            'status' => [
                'type' => OrderStatusTypeEnum::list(),
                'description' => 'Filter by order status',
            ],
            'project_id' => [
                'type' => Type::listOf(
                    NonNullType::id()
                ),
                'description' => 'Filter by project'
            ],
            'query' => [
                'type' => Type::string(),
                'description' => 'Filter by model/order id',
            ],
            'page' => [
                'type' => Type::int(),
                'defaultValue' => 1
            ]
        ];
    }

    /**
     * @return Type
     */
    public function type(): Type
    {
        return $this->paginateType(
            OrderType::type()
        );
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return LengthAwarePaginator
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->orderService->getList($args, $this->user());
    }
}
