<?php

namespace App\GraphQL\Mutations\BackOffice\Orders;

use App\Dto\Orders\OrderDto;
use App\GraphQL\InputTypes\Orders\BackOffice\OrderBackOfficeInput;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Orders\OrderType;
use App\Models\Orders\Order;
use App\Models\Technicians\Technician;
use App\Permissions\Orders\OrderUpdatePermission;
use App\Rules\Orders\OrderPartRule;
use App\Services\Orders\OrderService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class OrderUpdateMutation extends BaseMutation
{
    public const NAME = 'orderUpdate';
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
                    $this->existNoDeleted(Order::class)
                ]
            ],
            'technician_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'integer',
                    $this->existNoDeleted(Technician::class)
                ]
            ],
            'order' => [
                'type' => OrderBackOfficeInput::nonNullType()
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
            fn() => $this->orderService->update(
                $args['id'],
                OrderDto::byArgs($args['order']),
                Technician::find($args['technician_id']),
                $this->user()
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'order.parts.*' => [
                new OrderPartRule()
            ]
        ];
    }
}
