<?php

namespace WezomCms\Orders\Repositories;

use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Throwable;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Repositories\AbstractRepository;
use WezomCms\Orders\Cart\Storage\AbstractStorage;
use WezomCms\Orders\Conditions\BonusCondition;
use WezomCms\Orders\Contracts\CartInterface;
use WezomCms\Orders\Drivers\Delivery\SdekCourier;
use WezomCms\Orders\Enums\PayedModes;
use WezomCms\Orders\Events\CreatedOrders;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderDeliveryInformation;
use WezomCms\Orders\Models\OrderItem;
use WezomCms\Orders\Models\OrderPaymentInformation;
use WezomCms\Orders\Models\OrderStatus;
use WezomCms\Orders\Models\Payment;
use Auth;
use WezomCms\Orders\Services\BonusService;
use WezomCms\Providers\Models\Provider;
use WezomCms\Users\Models\User;

class OrdersRepository extends AbstractRepository
{
    public function __construct(private BonusService $bonusService)
    {
        parent::__construct();
    }

    protected function query(): Builder
    {
        return Order::query();
    }

    public function setOrderStatus(int $order, OrderStatus $status): Order
    {
        /** @var Order $order */
        $order = $this->query()->where('id', $order)->first();

        $order->changeStatus($status);
        $order->save();

        return $order->refresh();
    }

    private function createDeliveryInformation(CartInterface $cart, Order $order, array $deliveryData): void
    {
        /** @var OrderDeliveryInformation $deliveryInformation*/
        $deliveryInformation = $order->deliveryInformation()->make();
        $deliveryInformation->region_code = array_get($deliveryData, 'region_code');
        $deliveryInformation->city_code = array_get($deliveryData, 'city_code');
        $deliveryInformation->branch_code = array_get($deliveryData, 'branch_code');
        $deliveryInformation->postal_code = array_get($deliveryData, 'postal_code');
        $deliveryInformation->tariff_code = array_get($deliveryData, 'tariff_code');
        $deliveryInformation->address = array_get($deliveryData, 'address');
        $deliveryInformation->time = array_get($deliveryData, 'time');
        $deliveryInformation->city = array_get($deliveryData, 'city');
        $deliveryInformation->street = array_get($deliveryData, 'street');
        $deliveryInformation->house = array_get($deliveryData, 'house');
        $deliveryInformation->room = array_get($deliveryData, 'room');
        $deliveryInformation->delivery_cost = $cart->getDeliveryCost($deliveryData['tariff_code'], $order->provider_id);

        $deliveryInformation->save();
    }

    public function createOrders($validatedData)
    {
        /** @var AbstractStorage $cart */
        $cart = app(CartInterface::class);

        try {
            $result = DB::transaction(function () use ($cart, $validatedData) {
                $orders = collect();

                /** @var User $user */
                $user = Auth::user();
                $totalBonuses = 0;

                if ($user && $useBonus = $validatedData->get('use_bonus')) {
                    $bonusCondition = new BonusCondition($useBonus, $cart->total());
                    $cart->applyCondition(new BonusCondition($useBonus, $cart->total()));

                    $totalBonuses = $bonusCondition->getUsedBonuses();
                }

                $payment = Payment::published()->find(array_get($validatedData, 'payment_id'));
                $paymentInformation = OrderPaymentInformation::create();

                foreach ($cart->separatedContent() as $providerId => $cartItems) {
                    /** @var Provider $provider */
                    if (!$provider = Provider::query()->where('admin_id', $providerId)->first()) {
                        continue;
                    }

                    $order = Order::create();

                    $order->paymentInformation()->associate($paymentInformation);

                    $order->provider_id = $provider->id;
                    $order->delivery_id = array_get($validatedData, 'delivery_id');
                    $this->createDeliveryInformation($cart, $order, array_get($validatedData, 'delivery_data'));

                    if ($user) {
                        $order->user()->associate($user);

                        $order->createClient();
                    }

                    // Recipient
                    $recipient = $validatedData->get('recipient');
                    $recipientData = [
                        'recipient_is_me' => array_get($recipient, 'recipient_is_me', false),
                        'comment' => array_get($recipient, 'comment'),
                    ];

                    if (!array_get($recipient, 'recipient_is_me', false)) {
                        $recipientData['name'] = array_get($recipient, 'name');
                        $recipientData['surname'] = array_get($recipient, 'surname');
                        $recipientData['phone'] = remove_phone_mask(array_get($recipient, 'phone'));
                        $recipientData['email'] = array_get($recipient, 'email');
                    }

                    $order->recipient()->create($recipientData);

                    // Set order status as new.
                    $order->changeStatus(OrderStatus::newStatus());

                    foreach ($cartItems as $cartItem) {
                        /** @var Product $product */
                        $product = $cartItem->getPurchaseItem();

                        /** @var OrderItem $orderItem */
                        $order->items()
                            ->create([
                                'product_id' => $product->id,
                                'quantity' => $cartItem->getQuantity(),
                                'price' => $product->basePrice(),
                                'purchase_price' => $product->priceForPurchase(),
                            ]);
                    }

                    if ($user && $totalBonuses > 0) {
                        $orderBonuses = min($totalBonuses, $order->getTotalSum());
                        $this->bonusService->createOrderBonusHistory(
                            $order,
                            $orderBonuses
                        );

                        if ($orderBonuses >= $order->getTotalSum()) {
                            $order->setPaid(PayedModes::AUTO);
                        }

                        $totalBonuses -= $orderBonuses;
                    }

                    // Store payment
                    if ($payment) {
                        $order->payment()->associate($payment);

                        if ($driver = $payment->makeDriver()) {
                            $driver->handleOrder($order);
                        }
                    }

                    if (method_exists($this, 'afterCreationOrder')) {
                        $this->afterCreationOrder($order);
                    }

                    $order->save();

                    $order->fresh();

                    $orders->push($order);
                }

                if ($orders->isEmpty()) {
                    throw new Exception(__('cms-orders::site.cart.Cart is empty'));
                }

                $paymentInformation->setOrderIds()->save();

                event(new CreatedOrders($orders));

                return $paymentInformation;
            }, 3);

            // Clear cart
            $cart->clear();

            return $result;

        } catch (Throwable $exception) {
            logger('Orders creation error', ['message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]);

            return $exception;
        }
    }

    public function getSdekOrdersWithoutTtn(): Collection
    {
        return Order::query()
            ->whereHas(
                'delivery',
                fn (Builder $query) => $query->where('driver', SdekCourier::KEY)
            )
            ->whereHas(
                'deliveryInformation',
                fn (Builder $query) => $query->whereNotNull('uuid')->whereNull('ttn')
            )
            ->get();
    }
}
