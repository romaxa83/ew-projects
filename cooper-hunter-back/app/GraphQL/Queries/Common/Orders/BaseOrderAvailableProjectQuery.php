<?php


namespace App\GraphQL\Queries\Common\Orders;


use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Projects\ProjectType;
use App\Models\Orders\Order;
use App\Models\Projects\Project;
use App\Permissions\Orders\OrderListPermission;
use App\Services\Orders\OrderService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseOrderAvailableProjectQuery extends BaseQuery
{

    public const NAME = 'orderAvailableProject';
    public const PERMISSION = OrderListPermission::KEY;

    public function __construct(protected OrderService $orderService)
    {
        $this->setMutationGuard();
    }

    abstract protected function setMutationGuard(): void;

    /**
     * @return array[]
     */
    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rule' => [
                    Rule::exists(Order::class, 'id')
                ]
            ]
        ];
    }

    /**
     * @return Type
     */
    public function type(): Type
    {
        return ProjectType::type();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Project|null
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ?Project {
        return $this->orderService->getAvailableProject($args['id'], $this->user());
    }
}
