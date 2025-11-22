<?php

namespace App\GraphQL\Mutations\FrontOffice\Orders\Dealer;

use App\Dto\Orders\Dealer\OrderDto;
use App\Enums\Orders\Dealer\PaymentType;
use App\GraphQL\InputTypes\Orders\Dealer;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Orders\Dealer\OrderType;
use App\Models\Orders\Dealer\Order;
use App\Permissions\Orders\Dealer\UpdatePermission;
use App\Repositories\Orders\Dealer\OrderRepository;
use App\Rules\Orders\Dealer\UniqPORule;
use App\Services\Orders\Dealer\OrderService;
use Core\GraphQL\Mutations\BaseMutation;
use Core\Traits\Auth\DealerInspector;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class UpdateMutation extends BaseMutation
{
    use DealerInspector;

    public const NAME = 'dealerOrderUpdate';
    public const PERMISSION = UpdatePermission::KEY;

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
            'order' => [
                'type' => Dealer\OrderInput::type(),
                'rules' => ['required', 'array']
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

        $this->canUpdateOrder($model);

        $dto = OrderDto::byArgs($args['order']);

        $model = makeTransaction(
            fn(): Order => $this->service->update($model, $dto)
        );

        return $model;
    }

    protected function rules(array $args = []): array
    {
        return [
            'order.payment_card_id' => [
                'required_if:order.payment_type,' . PaymentType::CARD
            ],
            'order.po' => [
                'nullable', new UniqPORule($this->user(), $args)
            ]
        ];
    }
}

