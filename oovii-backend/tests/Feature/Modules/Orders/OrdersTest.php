<?php

namespace Tests\Feature\Modules\Orders;


use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Notification;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Models\Administrator;
use WezomCms\Core\Models\Role;
use WezomCms\Firebase\Events\FcmPush;
use WezomCms\Firebase\Models\FcmNotification;
use WezomCms\Firebase\Models\Template;
use WezomCms\Orders\Cart\Storage\DatabaseStorage;
use WezomCms\Orders\Contracts\CartInterface;
use WezomCms\Orders\Drivers\Delivery\SdekCourier;
use WezomCms\Orders\Drivers\Payment\Bonus;
use WezomCms\Orders\Drivers\Payment\PayBox;
use WezomCms\Orders\Enums\PayedModes;
use WezomCms\Orders\Events\CreatedOrders;
use WezomCms\Orders\Models\Delivery;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderClient;
use WezomCms\Orders\Models\OrderDeliveryInformation;
use WezomCms\Orders\Models\OrderItem;
use WezomCms\Orders\Models\OrderPaymentInformation;
use WezomCms\Orders\Models\OrderRecipient;
use WezomCms\Orders\Models\OrderStatus;
use WezomCms\Orders\Models\Payment;
use WezomCms\Orders\Notifications\CancelOrderNotification;
use WezomCms\Orders\Notifications\CreatedOrderNotification;
use WezomCms\Providers\Models\Provider;
use WezomCms\Providers\Types\ProviderStatus;
use WezomCms\Users\Enums\BonusHistoryType;
use WezomCms\Users\Models\BonusHistory;
use WezomCms\Users\Models\User;

class OrdersTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    public function test_it_can_create_new_order(): void
    {
        $user = $this->loginAsUser();
        $cart = $this->prepareCart();

        $data = [
            'delivery_id' => $cart['delivery_id'],
            'payment_id' => $cart['payment_id'],
            'delivery_data' => $this->getDeliveryData(),
            'recipient' => $this->getRecipientData(),
        ];

        Event::fake();

        $res = $this->postJson(
            route('api.v1.mobile.checkout.create-order'),
            $data,
            [ 'Cart-hash' => $cart['cartHash'] ]
        )
            ->assertStatus(200)
            ->assertJsonStructure($this->getExpectedJsonStructure());

        $payment = $res->json('data');

        $dbPayment = OrderPaymentInformation::first();

        self::assertEquals($payment['total_sum'], $dbPayment->getTotalSum());

        $this->checkOrders($payment['orders'], $cart, $user);
        $this->checkRecipient($payment['orders'], $data);
        $this->checkDelivery($payment['orders']);

        $this->assertDatabaseCount(OrderPaymentInformation::TABLE, 1);
    }

    public function test_it_returns_payment_driver_payload(): void
    {
        $this->loginAsUser();
        $cart = $this->prepareCart(PayBox::KEY);

        $data = [
            'delivery_id' => $cart['delivery_id'],
            'payment_id' => $cart['payment_id'],
            'delivery_data' => $this->getDeliveryData(),
            'recipient' => $this->getRecipientData(),
        ];

        Event::fake();

        $res = $this->postJson(
            route('api.v1.mobile.checkout.create-order'),
            $data,
            [ 'Cart-hash' => $cart['cartHash'] ]
        )
            ->assertOk();

        $paymentPayload = $res->json('data.payment_payload');

        self::assertEquals(PayBox::KEY, $paymentPayload['payment_driver']);
        self::assertEquals(route('api.v1.mobile.pay-box.check'), $paymentPayload['check_url']);
        self::assertEquals(route('api.v1.mobile.pay-box.result'), $paymentPayload['result_url']);
    }

    public function test_it_takes_delivery_cost_from_cart_on_creating_order(): void
    {
        $this->loginAsUser();
        $cart = $this->prepareCart();

        $cartInterface = $cart['cart'];
        $cartInterface->getMainCart()->delivery_data = [
            139 => [
                $cart['provider1']->id => 620,
                $cart['provider2']->id => 450,
            ],
        ];

        $data = [
            'delivery_id' => $cart['delivery_id'],
            'payment_id' => $cart['payment_id'],
            'delivery_data' => $this->getDeliveryData(),
            'recipient' => $this->getRecipientData(),
        ];

        Event::fake();

        $res = $this->postJson(
            route('api.v1.mobile.checkout.create-order'),
            $data,
            [ 'Cart-hash' => $cart['cartHash'] ]
        )
            ->assertStatus(200);

        $orders = $res->json('data.orders');

        $this->assertDatabaseHas(
            OrderDeliveryInformation::TABLE,
            [
                'order_id' => $orders[0]['id'],
                'region_code' => $data['delivery_data']['region_code'],
                'city_code' => $data['delivery_data']['city_code'],
                'postal_code' => $data['delivery_data']['postal_code'],
                'address' => $data['delivery_data']['address'],
                'tariff_code' => $data['delivery_data']['tariff_code'],
                'delivery_cost' => 620,
            ]
        );

        $this->assertDatabaseHas(
            OrderDeliveryInformation::TABLE,
            [
                'order_id' => $orders[1]['id'],
                'region_code' => $data['delivery_data']['region_code'],
                'city_code' => $data['delivery_data']['city_code'],
                'postal_code' => $data['delivery_data']['postal_code'],
                'address' => $data['delivery_data']['address'],
                'tariff_code' => $data['delivery_data']['tariff_code'],
                'delivery_cost' => 450,
            ]
        );
    }

    public function test_it_returns_recipient_data_validation_errors(): void
    {
        $this->loginAsUser();
        $cart = $this->prepareCart();
        $recipientData = $this->getRecipientData();
        $recipientData['name'] = null;

        $data = [
            'payment_id' => $cart['payment_id'],
            'delivery_id' => $cart['delivery_id'],
            'delivery_data' => $this->getDeliveryData(),
            'recipient' => $recipientData,
        ];

        $this->postJson(
            route('api.v1.mobile.checkout.create-order'),
            $data,
            [ 'Cart-hash' => $cart['cartHash'] ]
        )
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(
                __('validation.required_if', [
                    'attribute' => __('cms-orders::site.recipient.Name'),
                    'other' => __('cms-orders::site.recipient.Recipient is me'),
                    'value' => 'false',
                ])
            ));

        $data['recipient']['name'] = 'Vasya';
        $data['recipient']['phone'] = '12345';

        $this->postJson(
            route('api.v1.mobile.checkout.create-order'),
            $data,
            [ 'Cart-hash' => $cart['cartHash'] ]
        )
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(
                __('cms-core::admin.validation.wrong_phone_format')
            ));
    }

    public function test_it_can_pay_for_order_with_user_bonuses(): void
    {
        $user = $this->loginAsUser();
        $user->createCorrectionTransaction(2000);

        $cart = $this->prepareCart(Bonus::KEY);
        $user->refresh();

        $data = [
            'delivery_id' => $cart['delivery_id'],
            'payment_id' => $cart['payment_id'],
            'delivery_data' => $this->getDeliveryData(),
            'recipient' => $this->getRecipientData(),
        ];

        Event::fake();

        $res = $this->postJson(
            route('api.v1.mobile.checkout.create-order'),
            $data,
            [ 'Cart-hash' => $cart['cartHash'] ]
        )
            ->assertStatus(200);

        $orders = $res->json('data.orders');

        foreach ($orders as $order) {
            self::assertTrue($order['payed']);

            $this->assertDatabaseHas(
                Order::TABLE,
                [
                    'id' => $order['id'],
                    'payed' => true,
                    'payed_mode' => PayedModes::AUTO,
                    'status_id' => OrderStatus::PAID,
                ]
            );

            $this->assertDatabaseHas(
                'order_status_history',
                [
                    'order_id' => $order['id'],
                    'status_id' => OrderStatus::PAID,
                ]
            );

            $this->assertDatabaseHas(
                BonusHistory::TABLE,
                [
                    'inviter_id' => $user->id,
                    'order_id' => $order['id'],
                    'type' => BonusHistoryType::USE,
                ]
            );
        }

        $this->assertDatabaseCount('order_status_history', 4);
    }

    public function test_it_returns_correct_orders_sum_with_using_user_bonuses(): void
    {
        $user = $this->loginAsUser();
        $user->createCorrectionTransaction(1500);

        $cart = $this->prepareCart(PayBox::KEY);
        $user->refresh();

        $data = [
            'delivery_id' => $cart['delivery_id'],
            'payment_id' => $cart['payment_id'],
            'delivery_data' => $this->getDeliveryData(),
            'recipient' => $this->getRecipientData(),
            'use_bonus' => 1000,
        ];

        Event::fake();

        $res = $this->postJson(
            route('api.v1.mobile.checkout.create-order'),
            $data,
            [ 'Cart-hash' => $cart['cartHash'] ]
        )
            ->assertStatus(200);

        $orders = $res->json('data.orders');

        $order1 = $orders[0];

        self::assertTrue($order1['payed']);
        self::assertEquals(0, $order1['sum']);

        $this->assertDatabaseHas(
            Order::TABLE,
            [
                'id' => $order1['id'],
                'payed' => true,
                'payed_mode' => PayedModes::AUTO,
            ]
        );

        $this->assertDatabaseHas(
            BonusHistory::TABLE,
            [
                'inviter_id' => $user->id,
                'order_id' => $order1['id'],
                'type' => BonusHistoryType::USE,
                'bonus' => 900,
            ]
        );

        $order2 = $orders[1];

        self::assertFalse($order2['payed']);
        self::assertEquals(500, $order2['sum']);

        $this->assertDatabaseHas(
            Order::TABLE,
            [
                'id' => $order2['id'],
                'payed' => false,
                'payed_mode' => null,
            ]
        );

        $this->assertDatabaseHas(
            BonusHistory::TABLE,
            [
                'inviter_id' => $user->id,
                'order_id' => $order2['id'],
                'type' => BonusHistoryType::USE,
                'bonus' => 100,
            ]
        );
    }

    public function test_it_cant_pay_for_order_if_user_has_not_enough_bonuses(): void
    {
        $user = $this->loginAsUser();
        $user->createCorrectionTransaction(500);

        $cart = $this->prepareCart(Bonus::KEY);
        $user->refresh();

        $data = [
            'delivery_id' => $cart['delivery_id'],
            'payment_id' => $cart['payment_id'],
            'delivery_data' => $this->getDeliveryData(),
            'recipient' => $this->getRecipientData(),
        ];

        Event::fake();

        $res = $this->postJson(
            route('api.v1.mobile.checkout.create-order'),
            $data,
            [ 'Cart-hash' => $cart['cartHash'] ]
        )
            ->assertStatus(200);

        $orders = $res->json('data.orders');

        foreach ($orders as $order) {
            $this->assertDatabaseHas(
                Order::TABLE,
                [
                    'id' => $order['id'],
                    'payed' => false,
                    'payed_mode' => null,
                    'status_id' => OrderStatus::NEW,
                ]
            );
        }

        $this->assertDatabaseMissing(
            BonusHistory::TABLE,
            [
                'inviter_id' => $user->id,
                'type' => BonusHistoryType::USE,
            ]
        );
    }

    private function prepareCart(?string $paymentDriver = null): array
    {
        /** @var Delivery $delivery */
        $delivery = Delivery::factory()->create(['driver' => SdekCourier::KEY]);

        /** @var Payment $payment */
        $payment = Payment::factory()->create([
            'driver' => $paymentDriver,
        ]);

        $cartHash = sha1(microtime() . Str::random());
        config([ 'cms.orders.cart.hash' => $cartHash ]);
        /** @var DatabaseStorage $cart */
        $cart = app(CartInterface::class);

        /** @var Provider $provider1 */
        $provider1 = Provider::factory()->create([
            'status' => ProviderStatus::MODERATED,
            'active' => true,
            'admin_id' => Administrator::factory()->create([ 'super_admin' => false ])->id,
        ]);
        /** @var Provider $provider2 */
        $provider2 = Provider::factory()->create([
            'status' => ProviderStatus::MODERATED,
            'active' => true,
            'admin_id' => Administrator::factory()->create([ 'super_admin' => false ])->id,
        ]);

        $cart->getMainCart()->save();
        /** @var Product $product1 */
        $product1 = Product::factory()->create([
            'cost' => 500,
            'cost_discount' => 450,
            'provider_id' => $provider1->admin_id,
        ]);
        /** @var Product $product2 */
        $product2 = Product::factory()->create([
            'cost' => 200,
            'cost_discount' => 0.0,
            'provider_id' => $provider2->admin_id,
        ]);

        $cart->add($product1, 2);
        $cart->add($product2, 3);

        return [
            'delivery_id' => $delivery->id,
            'payment_id' => $payment->id,
            'cartHash' => $cartHash,
            'product1' => $product1,
            'provider1' => $provider1,
            'product2' => $product2,
            'provider2' => $provider2,
            'cart' => $cart,
        ];
    }

    private function getRecipientData(bool $isMe = false): array
    {
        return [
            'recipient_is_me' => $isMe,
            'name' => $isMe ? null : 'RecipientName',
            'surname' => $isMe ? null : 'RecipientSurname',
            'phone' => $isMe ? null : '+380501234567',
            'email' => $isMe ? null : 'recipient@gmail.com',
            'comment' => 'Комментарий к заказу',
        ];
    }

    private function getDeliveryData(): array
    {
        return [
            'region_code' => '299',
            'city_code' => '4756',
            'postal_code' => '050054',
            'address' => 'Ул. Какая-то, 28',
            'tariff_code' => current(config('cms.orders.delivery-and-payment.sdek.tariffs', [])),
            'time' => '10:00 - 11:00',
        ];
    }

    private function getExpectedJsonStructure(): array
    {
        return [
            'success',
            'data' => [
                'id',
                'payment_payload',
                'payment_data',
                'orders' => [
                    '*' => [
                        'id',
                        'payed',
                        'sum',
                    ]
                ],
            ],
        ];
    }

    private function checkRecipient(array $orders, array $data): void
    {
        foreach ($orders as $order) {
            $this->assertDatabaseHas(
                OrderRecipient::TABLE,
                [
                    'order_id' => $order['id'],
                    'recipient_is_me' => $data['recipient']['recipient_is_me'],
                    'name' => $data['recipient']['name'],
                    'surname' => $data['recipient']['surname'],
                    'phone' => $data['recipient']['phone'],
                    'email' => $data['recipient']['email'],
                    'comment' => $data['recipient']['comment'],
                ]
            );
        }
    }

    private function checkDelivery(array $orders): void
    {
        $deliveryData = $this->getDeliveryData();

        foreach ($orders as $order) {
            $this->assertDatabaseHas(
                OrderDeliveryInformation::TABLE,
                [
                    'order_id' => $order['id'],
                    'region_code' => $deliveryData['region_code'],
                    'city_code' => $deliveryData['city_code'],
                    'postal_code' => $deliveryData['postal_code'],
                    'address' => $deliveryData['address'],
                    'tariff_code' => $deliveryData['tariff_code'],
                    'time' => $deliveryData['time'],
                ]
            );
        }
    }

    private function checkOrders(array $orders, array $cart, User $user): void
    {
        self::assertCount(2, $orders);

        foreach ($orders as $index => $order) {
            self::assertFalse($order['payed']);

            $providerId = $index === 0 ? $cart['provider1']->id : $cart['provider2']->id;

            $this->assertDatabaseHas(
                Order::TABLE,
                [
                    'id' => $order['id'],
                    'delivery_id' => $cart['delivery_id'],
                    'payment_id' => $cart['payment_id'],
                    'status_id' => OrderStatus::NEW,
                    'user_id' => $user->id,
                    'provider_id' => $providerId,
                ]
            );
        }

        $this->assertDatabaseHas(
            OrderItem::TABLE,
            [
                'order_id' => $orders[0]['id'],
                'quantity' => 2,
                'price' => $cart['product1']->basePrice(),
                'purchase_price' => $cart['product1']->priceForPurchase(),
            ]
        );

        $this->assertDatabaseHas(
            OrderItem::TABLE,
            [
                'order_id' => $orders[1]['id'],
                'quantity' => 3,
                'price' => $cart['product2']->priceForPurchase(),
                'purchase_price' => $cart['product2']->priceForPurchase(),
            ]
        );
    }

    public function test_it_dispatches_created_order_event(): void
    {
        $this->loginAsUser();
        $cart = $this->prepareCart();

        $data = [
            'delivery_id' => $cart['delivery_id'],
            'payment_id' => $cart['payment_id'],
            'delivery_data' => $this->getDeliveryData(),
            'recipient' => $this->getRecipientData(),
        ];

        Event::fake(CreatedOrders::class);

        $res = $this->postJson(
            route('api.v1.mobile.checkout.create-order'),
            $data,
            [ 'Cart-hash' => $cart['cartHash'] ]
        );

        $orderIds = $res->json('data.orders');

        Event::assertDispatched(CreatedOrders::class, function (CreatedOrders $event) use ($orderIds) {
            $eventIds = $event->orders->pluck('id');
            $requestIds = collect($orderIds)->pluck('id');

            return $eventIds->diff($requestIds)->isEmpty();
        });
    }

    public function test_it_sends_notifications_on_creating_order(): void
    {
        $this->loginAsUser();
        $cart = $this->prepareCart();

        $data = [
            'delivery_id' => $cart['delivery_id'],
            'payment_id' => $cart['payment_id'],
            'delivery_data' => $this->getDeliveryData(),
            'recipient' => $this->getRecipientData(),
        ];

        /** @var Role $role */
        $role = Role::factory()->create([ 'name' => Role::DEFAULT_PROVIDER, 'permissions' => ['orders.edit'] ]);
        $cart['provider1']->adminProfile->roles()->attach($role->id);
        $cart['provider2']->adminProfile->roles()->attach($role->id);

        Notification::fake();

        $this->postJson(
            route('api.v1.mobile.checkout.create-order'),
            $data,
            [ 'Cart-hash' => $cart['cartHash'] ]
        );

        // Creating wrong admins
        Administrator::factory()
            ->count(2)
            ->create([
                'super_admin' => false,
            ])
            ->each(function (Administrator $admin) use ($role) {
                $admin->roles()->attach($role->id);
            });

        $admins = Administrator::query()
            ->where(function ($query) {
                $query
                    ->where('super_admin', 1)
                    ->orWhereHas('products');
            })
            ->get();

        Notification::assertSentTo(
            $admins,
            CreatedOrderNotification::class,
        );

        Notification::assertSentTimes(CreatedOrderNotification::class, 4);

        /*Notification::assertSentTo(
            [$user],
            UserCreatedOrdersNotification::class,
        );*/
    }

    public function test_user_can_cancel_his_order(): void
    {
        $user = $this->loginAsUser();
        /** @var Order $order */
        $order = Order::factory()->create([ 'user_id' => $user->id ]);
        OrderClient::factory()->create([ 'order_id' => $order->id ]);

        self::assertEquals(OrderStatus::newStatus()->id, $order->status_id);

        $this->getJson(route('api.v1.mobile.checkout.cancel-order', [ 'order' => $order->id ]))
            ->assertOk();

        $order->refresh();

        self::assertEquals(OrderStatus::canceledStatus()->id, $order->status_id);

        $this->assertDatabaseHas(
            'order_status_history',
            [
                'order_id' => $order->id,
                'status_id' => OrderStatus::canceledStatus()->id,
            ]
        );
    }

    public function test_user_can_cancel_only_his_own_order(): void
    {
        $orderOwner = User::factory()->create();
        $this->loginAsUser();
        /** @var Order $order */
        $order = Order::factory()->create([ 'user_id' => $orderOwner->id ]);
        OrderClient::factory()->create([ 'order_id' => $order->id ]);

        $this->getJson(route('api.v1.mobile.checkout.cancel-order', [ 'order' => $order->id ]))
            ->assertForbidden()
            ->assertJson($this->structureErrorResponse('This action is unauthorized.'));
    }

    public function test_it_sends_notifications_on_canceling_order(): void
    {
        $user = $this->loginAsUser();
        /** @var Order $order */
        $order = Order::factory()->create([ 'user_id' => $user->id ]);

        Notification::fake();

        $this->getJson(route('api.v1.mobile.checkout.cancel-order', [ 'order' => $order->id ]));

        $admins = Administrator::toNotifications(['orders.edit', 'orders.show'])->get();

        Notification::assertSentTo(
            $admins,
            CancelOrderNotification::class,
        );
    }

    public function test_it_dispatches_fcm_push_event_on_change_order_status(): void
    {
        $user = $this->loginAsUser();

        Notification::fake();
        Event::fake([FcmPush::class]);

        /** @var Order $order */
        $order = Order::factory()->create([ 'user_id' => $user->id ]);

        Event::assertDispatched(FcmPush::class, function (FcmPush $event) use ($order) {
            return $order->user_id === $event->getUser()->id
                && Template::TYPE_ORDER_CHANGE_STATUS === $event->getType()
                && $order->id === $event->getModel()->id
                && $order->status_id === OrderStatus::NEW;
        });

        $order->changeStatus(OrderStatus::paidStatus())->save();

        Event::assertDispatched(FcmPush::class, function (FcmPush $event) use ($order) {
            return $order->user_id === $event->getUser()->id
                && Template::TYPE_ORDER_CHANGE_STATUS === $event->getType()
                && $order->id === $event->getModel()->id
                && $order->status_id === OrderStatus::PAID;
        });

        Event::assertDispatched(FcmPush::class, 2);
    }

    public function test_it_creates_fcm_notification_on_creating_order(): void
    {
        $user = $this->loginAsUser();
        $cart = $this->prepareSimpleCart();

        $data = [
            'delivery_id' => $cart['delivery_id'],
            'payment_id' => $cart['payment_id'],
            'delivery_data' => $this->getDeliveryData(),
            'recipient' => $this->getRecipientData(),
        ];

        Event::fake(CreatedOrders::class);

        $res = $this->postJson(
            route('api.v1.mobile.checkout.create-order'),
            $data,
            [ 'Cart-hash' => $cart['cartHash'] ]
        );

        $notifications = FcmNotification::all();
        self::assertCount(1, $notifications);

        $this->assertDatabaseCount(FcmNotification::TABLE, 1);

        $this->assertDatabaseHas(
            FcmNotification::TABLE,
            [
                'user_id' => $user->id,
                'entity_type' => Order::class,
            ]
        );
    }

    private function prepareSimpleCart(): array
    {
        /** @var Delivery $delivery */
        $delivery = Delivery::factory()->create(['driver' => SdekCourier::KEY]);

        /** @var Payment $payment */
        $payment = Payment::factory()->create();

        $cartHash = sha1(microtime() . Str::random());
        config([ 'cms.orders.cart.hash' => $cartHash ]);
        /** @var DatabaseStorage $cart */
        $cart = app(CartInterface::class);

        /** @var Provider $provider1 */
        $provider1 = Provider::factory()->create([
            'status' => ProviderStatus::MODERATED,
            'active' => true,
            'admin_id' => Administrator::factory()->create([ 'super_admin' => false ])->id,
        ]);

        $cart->getMainCart()->save();
        /** @var Product $product1 */
        $product1 = Product::factory()->create([
            'cost' => 500,
            'cost_discount' => 450,
            'provider_id' => $provider1->admin_id,
        ]);

        $cart->add($product1, 2);

        return [
            'delivery_id' => $delivery->id,
            'payment_id' => $payment->id,
            'cartHash' => $cartHash,
            'product1' => $product1,
            'provider1' => $provider1,
            'cart' => $cart,
        ];
    }
}
