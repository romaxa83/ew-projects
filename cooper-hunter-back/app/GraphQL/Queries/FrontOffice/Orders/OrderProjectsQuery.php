<?php


namespace App\GraphQL\Queries\FrontOffice\Orders;


use App\Enums\Orders\OrderFilterTabEnum;
use App\GraphQL\Types\Enums\Orders\OrderFilterTabTypeEnum;
use App\GraphQL\Types\Projects\ProjectType;
use App\Permissions\Orders\OrderListPermission;
use App\Services\Orders\OrderService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Support\SelectFields;

class OrderProjectsQuery extends BaseQuery
{

    public const NAME = 'orderProjects';
    public const PERMISSION = OrderListPermission::KEY;

    public function __construct(protected OrderService $orderService)
    {
        $this->setTechnicianGuard();
    }

    public function args(): array
    {
        return [
            'tab' => [
                'type' => OrderFilterTabTypeEnum::type(),
                'defaultValue' => OrderFilterTabEnum::ACTIVE
            ]
        ];
    }

    /**
     * @return Type
     */
    public function type(): Type
    {
        return ProjectType::list();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Collection|null
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): ?Collection
    {
        return $this->orderService->getOrderProjects($args, $this->user());
    }
}
