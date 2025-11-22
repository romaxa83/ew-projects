<?php


namespace App\GraphQL\Queries\BackOffice\Orders;


use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Orders\OrderStatusHistoryType;
use App\Models\Orders\Order;
use App\Permissions\Orders\OrderListPermission;
use App\Services\Orders\OrderStatusService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class OrderStatusHistoryQuery extends BaseQuery
{
    public const NAME = 'orderStatusHistory';
    public const PERMISSION = OrderListPermission::KEY;

    public function __construct(protected OrderStatusService $orderStatusService)
    {
        $this->setAdminGuard();
    }

    /**
     * @return array[]
     */
    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'description' => 'Order ID',
                'rules' => [
                    'required',
                    Rule::exists(Order::class, 'id')
                        ->whereNull('deleted_at')
                ]
            ]
        ];
    }

    public function type(): Type
    {
        return OrderStatusHistoryType::nonNullList();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return $this->orderStatusService->getHistory($args['id']);
    }
}
