<?php

namespace App\GraphQL\Mutations\BackOffice\Orders;

use App\Dto\Orders\OrderDto;
use App\GraphQL\InputTypes\Orders\BackOffice\OrderBackOfficeInput;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Orders\OrderType;
use App\Models\Orders\Order;
use App\Models\Technicians\Technician;
use App\Permissions\Orders\OrderCreatePermission;
use App\Rules\Orders\OrderPartRule;
use App\Services\Orders\OrderService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class OrderCreateMutation extends BaseMutation
{
    public const NAME = 'orderCreate';
    public const PERMISSION = OrderCreatePermission::KEY;

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
            fn() => $this->orderService->create(
                OrderDto::byArgs($args['order']),
                Technician::query()->find($args['technician_id'])
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
