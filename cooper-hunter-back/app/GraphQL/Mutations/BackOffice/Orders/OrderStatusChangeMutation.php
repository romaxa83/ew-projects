<?php

namespace App\GraphQL\Mutations\BackOffice\Orders;

use App\GraphQL\Types\Enums\Orders\OrderStatusTypeEnum;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Orders\OrderType;
use App\Models\Orders\Order;
use App\Permissions\Orders\OrderUpdatePermission;
use App\Services\Orders\OrderService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class OrderStatusChangeMutation extends BaseMutation
{
    public const NAME = 'orderStatusChange';
    public const PERMISSION = OrderUpdatePermission::KEY;

    public function __construct(protected OrderService $orderService)
    {
        $this->setAdminGuard();
    }

    /**
     * @return Type
     */
    public function type(): Type
    {
        return OrderType::nonNullType();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    Rule::exists(Order::class, 'id')
                        ->whereNull('deleted_at')
                ]
            ],
            'status' => [
                'type' => OrderStatusTypeEnum::nonNullType(),
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Order
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Order
    {
        return makeTransaction(
            fn() => $this->orderService->changeStatus($args['id'], $args['status'], $this->user())
        );
    }
}
