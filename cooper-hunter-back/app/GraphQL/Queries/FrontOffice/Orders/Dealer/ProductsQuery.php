<?php

namespace App\GraphQL\Queries\FrontOffice\Orders\Dealer;

use App\GraphQL\Types\Orders\Dealer\ProductType;
use App\Models\Orders\Dealer\Order;
use App\Permissions\Orders\Dealer\CreatePermission;
use App\Repositories\Catalog\Product\ProductRepository;
use App\Repositories\Orders\Dealer\OrderRepository;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class ProductsQuery extends BaseQuery
{
    public const NAME = 'dealerProductOrderList';
    public const PERMISSION = CreatePermission::KEY;

    public function __construct(
        protected ProductRepository $repo,
        protected OrderRepository $repoOrder
    )
    {
        $this->setDealerGuard();
    }

    public function args(): array
    {
        return [
            'order_id' => [
                'type' => Type::id(),
                'rules' => ['nullable', Rule::exists(Order::TABLE, 'id')],
                'description' => 'DealerOrderType ID'
            ],
        ];
    }

    public function type(): Type
    {
        return ProductType::list();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection
    {
        /** @var $order null|Order */
        $order = null;
        if(isset($args['order_id'])){
            $order = $this->repoOrder->getBy('id', $args['order_id'], ['items']);
        }

        return $this->repo->getListForDealerOrder($this->user(), $order);
    }
}
