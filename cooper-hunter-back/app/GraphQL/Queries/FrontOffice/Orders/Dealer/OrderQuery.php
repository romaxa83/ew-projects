<?php

namespace App\GraphQL\Queries\FrontOffice\Orders\Dealer;

use App\GraphQL\Types\Orders\Dealer\OrderType;
use App\Models\Orders\Dealer\Order;
use App\Permissions\Orders\Dealer\ListPermission;
use App\Repositories\Orders\Dealer\OrderRepository;
use App\Traits\GraphQL\Order\Dealer\InitArgsForFilter;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class OrderQuery extends BaseQuery
{
    use InitArgsForFilter;

    public const NAME = 'dealerOrder';
    public const PERMISSION = ListPermission::KEY;

    public function __construct(
        protected OrderRepository $repo
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
        return OrderType::type();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Order
    {
        /** @var $order Order */
//        $order = $this->repo->getBy('id', $args['id'], $fields->getRelations());

        $args = $this->init($args);

        return $this->repo->getOneAccessibleToDealer($args['id'], data_get($args, 'dealer_id', []));
    }
}
