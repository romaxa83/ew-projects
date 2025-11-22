<?php


namespace App\GraphQL\Queries\Common\Orders\DeliveryTypes;


use App\GraphQL\Types\Orders\Deliveries\OrderDeliveryTypeType;
use App\Permissions\Orders\DeliveryTypes\OrderDeliveryTypeListPermission;
use App\Services\Orders\OrderDeliveryTypeService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseOrderDeliveryTypesQuery extends BaseQuery
{

    public const NAME = 'orderDeliveryTypes';
    public const PERMISSION = OrderDeliveryTypeListPermission::KEY;

    public function __construct(protected OrderDeliveryTypeService $orderDeliveryTypeService)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    /**
     * @return array[]
     */
    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::id(),
            ],
            'query' => [
                'type' => Type::string(),
                'description' => 'Field to filter by "title" or "description" fields',
            ],
        ];
    }

    /**
     * @return Type
     */
    public function type(): Type
    {
        return OrderDeliveryTypeType::nonNullList();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Collection
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Collection
    {
        return $this->orderDeliveryTypeService->getList($args, $this->user());
    }
}
