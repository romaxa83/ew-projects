<?php

namespace App\GraphQL\Mutations\BackOffice\Orders;

use App\Dto\Orders\OrderPartDto;
use App\GraphQL\InputTypes\Orders\BackOffice\OrderPartsBackOfficeInput;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Orders\OrderType;
use App\Models\Orders\Order;
use App\Permissions\Orders\OrderUpdatePermission;
use App\Rules\Orders\OrderPartRule;
use App\Services\Orders\OrderService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class OrderPartsUpdateMutation extends BaseMutation
{
    public const NAME = 'orderPartsUpdate';
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
            'parts' => [
                'type' => OrderPartsBackOfficeInput::nonNullList()
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
            fn() => $this->orderService->updateParts(
                $args['id'],
                array_map(
                    fn($item) => OrderPartDto::byArgs($item),
                    $args['parts']
                ),
                $this->user()
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'parts.*' => [
                new OrderPartRule()
            ]
        ];
    }
}
