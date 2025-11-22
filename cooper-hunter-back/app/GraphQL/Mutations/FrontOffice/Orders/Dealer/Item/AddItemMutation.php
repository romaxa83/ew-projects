<?php

namespace App\GraphQL\Mutations\FrontOffice\Orders\Dealer\Item;

use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Orders\Dealer\OrderType;
use App\Models\Catalog\Products\Product;
use App\Models\Companies\Price;
use App\Models\Orders\Dealer\Order;
use App\Permissions\Orders\Dealer\UpdatePermission;
use App\Repositories\Companies\CompanyPriceRepository;
use App\Repositories\Orders\Dealer\OrderRepository;
use App\Services\Orders\Dealer\OrderItemService;
use Core\GraphQL\Mutations\BaseMutation;
use Core\Traits\Auth\DealerInspector;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class AddItemMutation extends BaseMutation
{
    use DealerInspector;

    public const NAME = 'dealerOrderAddItem';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(
        protected OrderItemService $service,
        protected CompanyPriceRepository $priceRepository,
        protected OrderRepository $repo,
    )
    {
        $this->setDealerGuard();
    }

    public function args(): array
    {
        return [
            'order_id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', Rule::exists(Order::TABLE, 'id')],
                'description' => 'OrderType ID'
            ],
            'product_id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', Rule::exists(Product::TABLE, 'id')],
                'description' => 'ProductType ID'
            ],
        ];
    }

    public function type(): Type
    {
        return OrderType::nonNullType();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Order
    {
        $this->isNotForMainDealer();

        /** @var $model Order */
        $model = $this->repo->getBy('id', $args['order_id']);

        $this->canUpdateOrder($model);

        /** @var $price Price */
        $price = $this->priceRepository->getByFields([
            'company_id' => $this->user()->company_id,
            'product_id' => $args['product_id'],
        ]);

        makeTransaction(
            fn() => $this->service->add($model, $price)
        );

        return $model->refresh();
    }
}
