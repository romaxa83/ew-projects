<?php

namespace App\GraphQL\Mutations\FrontOffice\Orders;

use App\Dto\Orders\OrderDto;
use App\GraphQL\InputTypes\Orders\OrderInput;
use App\GraphQL\Types\Orders\OrderType;
use App\Models\Orders\Order;
use App\Models\Technicians\Technician;
use App\Permissions\Orders\OrderCreatePermission;
use App\Rules\Orders\OrderPartRule;
use App\Services\Orders\OrderService;
use Closure;
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
        $this->setTechnicianGuard();
    }

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool {
        return $this->can(static::PERMISSION) && $this->can('isActive', Technician::class);
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
            'order' => OrderInput::nonNullType()
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
