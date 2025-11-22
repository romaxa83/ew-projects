<?php

namespace App\GraphQL\Queries\FrontOffice\Orders\Dealer;

use App\Models\Orders\Dealer\Order;
use App\Permissions\Orders\Dealer\UpdatePermission;
use App\Repositories\Orders\Dealer\OrderRepository;
use App\Services\Orders\Dealer\OrderService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class ChangeProductPriceQuery extends BaseQuery
{
    public const NAME = 'dealerProductChangePrice';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(
        protected OrderRepository $repo,
        protected OrderService $service
    )
    {
        $this->setDealerGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::id(),
                'rules' => ['nullable', Rule::exists(Order::TABLE, 'id')],
                'description' => 'DealerOrderType ID'
            ],
        ];
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): bool
    {
        /** @var $order Order */
        $order = $this->repo->getBy('id', $args['id']);

        return $this->service->isChangeProductPrice($order, $this->user());
    }
}
