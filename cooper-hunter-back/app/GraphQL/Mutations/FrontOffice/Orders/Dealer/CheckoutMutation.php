<?php

namespace App\GraphQL\Mutations\FrontOffice\Orders\Dealer;

use App\Entities\Messages\ResponseMessageEntity;
use App\Events\Orders\Dealer\CheckoutOrderEvent;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\Orders\Dealer\Order;
use App\Permissions\Orders\Dealer\UpdatePermission;
use App\Repositories\Orders\Dealer\OrderRepository;
use App\Services\Orders\Dealer\OrderService;
use Core\GraphQL\Mutations\BaseMutation;
use Core\Traits\Auth\DealerInspector;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CheckoutMutation extends BaseMutation
{
    use DealerInspector;

    public const NAME = 'dealerOrderCheckout';
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
        ];
    }

    public function type(): Type
    {
        return ResponseMessageType::nonNullType();
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
    ): ResponseMessageEntity
    {
        try {
            $this->isNotForMainDealer();
            /** @var $order Order */
            $order = $this->repo->getBy('id', $args['id']);

            $this->isOwner($order);

            $this->checkOrder($order);

            if($order->guid){
                throw new \Exception(__('messages.dealer.order.checkout.order already sent'));
            }

            $order = $this->service->sendToOnec($order);

            if($order->status->isDraft()){
                $msg = __('exceptions.dealer.order.onec not create an order');
                if($order->error){
                    $msg = $order->error;
                }
                throw new \Exception($msg);
            }

            event(new CheckoutOrderEvent($order));

            $this->service->createPrimaryItems($order);

            return ResponseMessageEntity::success(__('messages.dealer.order.checkout.success'));
        } catch (\Throwable $e){
            return ResponseMessageEntity::warning($e->getMessage());
        }
    }

    public function checkOrder(Order $model): void
    {
        if(!$model->po){
            throw new \Exception(__('messages.dealer.order.checkout.po not specified'));
        }

        if($model->items->isEmpty()){
            throw new \Exception(__('messages.dealer.order.checkout.not items'));
        }
    }
}
