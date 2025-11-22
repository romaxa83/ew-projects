<?php

namespace App\GraphQL\Mutations\FrontOffice\Orders;

use App\Enums\Orders\OrderStatusEnum;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Orders\OrderType;
use App\Models\Orders\Order;
use App\Models\Technicians\Technician;
use App\Permissions\Orders\OrderUpdatePermission;
use App\Services\Orders\OrderService;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class OrderCanceledMutation extends BaseMutation
{
    public const NAME = 'orderCanceled';
    public const PERMISSION = OrderUpdatePermission::KEY;

    public function __construct(protected OrderService $orderService)
    {
        $this->setTechnicianGuard();
    }

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool {
        return $this->can(self::PERMISSION) && $this->can('isActive', Technician::class);
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
                        ->whereIn(
                            'status',
                            [
                                OrderStatusEnum::CREATED,
                                OrderStatusEnum::PENDING_PAID,
                                OrderStatusEnum::PAID
                            ]
                        )
                ]
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
            fn() => $this->orderService->setCanceledStatus(
                $args['id'],
                $this->user()
            )
        );
    }
}
