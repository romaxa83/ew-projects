<?php

namespace App\GraphQL\Mutations\BackOffice\Orders;

use App\GraphQL\Types\NonNullType;
use App\Models\Orders\Order;
use App\Permissions\Orders\OrderDeletePermission;
use App\Services\Orders\OrderService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class OrderDeleteMutation extends BaseMutation
{
    public const NAME = 'orderDelete';
    public const PERMISSION = OrderDeletePermission::KEY;

    public function __construct(protected OrderService $orderService)
    {
        $this->setAdminGuard();
    }

    /**
     * @return Type
     */
    public function type(): Type
    {
        return NonNullType::boolean();
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

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return bool
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return makeTransaction(
            fn() => $this->orderService->delete($args['id'], $this->user())
        );
    }
}
