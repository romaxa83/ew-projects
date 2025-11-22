<?php


namespace App\GraphQL\Mutations\Common\Orders;


use App\Models\Orders\Order;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

abstract class BaseOrderDisconnectProjectMutation extends BaseOrderProjectMutation
{

    public const NAME = 'orderDisconnectProject';

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
            fn() => $this->orderService->disconnectProject(
                $args['id'],
                $this->user()
            )
        );
    }
}
