<?php

namespace App\GraphQL\Mutations\Common\Orders;

use App\Dto\Orders\OrderShippingDto;
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

abstract class BaseOrderShippingUpdateMutation extends BaseMutation
{
    public const NAME = 'orderShippingUpdate';
    public const PERMISSION = OrderUpdatePermission::KEY;

    abstract protected function setMutationGuard(): void;

    abstract protected function notAvailableStatuses(): array;

    abstract protected function getShippingInputType(): Type;

    public function __construct(protected OrderService $orderService)
    {
        $this->setMutationGuard();
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
                        ->whereNotIn(
                            'status',
                            $this->notAvailableStatuses()
                        )
                        ->whereNull('deleted_at')
                ]
            ],
            'shipping' => [
                'type' => $this->getShippingInputType()
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
            fn() => $this->orderService->updateShippingData(
                $args['id'],
                $this->user(),
                OrderShippingDto::byArgs($args['shipping'])
            )
        );
    }

}
