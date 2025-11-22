<?php


namespace App\GraphQL\Mutations\Common\Orders;


use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Orders\OrderType;
use App\Models\Orders\Order;
use App\Permissions\Orders\OrderUpdatePermission;
use App\Services\Orders\OrderService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

abstract class BaseOrderProjectMutation extends BaseMutation
{

    public const PERMISSION = OrderUpdatePermission::KEY;

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
                        ->whereNull('deleted_at')
                ]
            ]
        ];
    }

    abstract protected function setMutationGuard(): void;
}
