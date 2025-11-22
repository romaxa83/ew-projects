<?php

namespace App\GraphQL\Mutations\FrontOffice\Orders\Dealer;

use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Orders\Dealer\OrderType;
use App\Models\Orders\Dealer\Order;
use App\Permissions\Orders\Dealer\CreatePermission;
use App\Repositories\Orders\Dealer\OrderRepository;
use App\Services\Orders\Dealer\OrderService;
use Core\GraphQL\Mutations\BaseMutation;
use Core\Traits\Auth\DealerInspector;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CopyMutation extends BaseMutation
{
    use DealerInspector;

    public const NAME = 'dealerOrderCopy';
    public const PERMISSION = CreatePermission::KEY;

    public function __construct(
        protected OrderService $service,
        protected OrderRepository $repo
    )
    {
        $this->setDealerGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', Rule::exists(Order::TABLE, 'id')],
                'description' => 'DealerOrderType ID'
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
        $model = $this->repo->getBy('id', $args['id']);

        $copy = makeTransaction(
            fn(): Order => $this->service->copy($model, $this->user())
        );

        return $copy;
    }
}
