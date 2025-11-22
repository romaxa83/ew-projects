<?php


namespace App\GraphQL\Mutations\Common\Orders;


use App\Models\Orders\Order;
use App\Models\Technicians\Technician;
use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

abstract class BaseOrderConnectProjectMutation extends BaseOrderProjectMutation
{

    public const NAME = 'orderConnectProject';

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
            fn() => $this->orderService->connectProject(
                $args['id'],
                $this->user()
            )
        );
    }
}
