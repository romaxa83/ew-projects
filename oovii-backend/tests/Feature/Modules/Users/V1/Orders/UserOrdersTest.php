<?php


namespace Tests\Feature\Modules\Users\V1\Orders;


use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Catalog\Models\Product;
use WezomCms\Orders\Drivers\Delivery\SdekCourier;
use WezomCms\Orders\Models\Delivery;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderDeliveryInformation;
use WezomCms\Orders\Models\OrderItem;
use WezomCms\Orders\Models\OrderRecipient;
use WezomCms\Orders\Models\OrderStatus;
use WezomCms\Providers\Models\Provider;
use WezomCms\Users\Enums\BonusHistoryType;

class UserOrdersTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    public function test_unauthenticated(): void
    {
        $this->getJson(route('api.v1.mobile.user.orders'), [])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse(__("cms-core::site.Unauthenticated")));
    }

    public function test_it_returns_paginated_user_order_list(): void
    {
        $user = $this->loginAsUser();
        $now = Carbon::now();
        Order::factory()
            ->count(8)
            ->sequence(fn ($sequence) => [ 'created_at' => $now->subDay() ])
            ->create([ 'user_id' => $user->id ])
            ->each(function (Order $order) {
                OrderRecipient::factory()->create(['order_id' => $order->id]);
            });

        $res = $this->getJson(route('api.v1.mobile.user.orders', [ 'per_page' => 2, 'page' => 3 ]))
            ->assertOk()
            ->assertJsonStructure($this->getOrdersResponseStructure());

        $data = $res->json('data');

        self::assertCount(2, $data['orders']);
        self::assertCount(4, $data['links']);
        self::assertEquals(3, $data['meta']['current_page']);
        self::assertEquals(5, $data['meta']['from']);
        self::assertEquals(4, $data['meta']['last_page']);
        self::assertEquals(2, $data['meta']['per_page']);
        self::assertEquals(6, $data['meta']['to']);
        self::assertEquals(8, $data['meta']['total']);
    }

    public function test_it_returns_user_order_full_info_by_id(): void
    {
        $user = $this->loginAsUser();
        $orders = Order::factory()
            ->count(5)
            ->create([ 'user_id' => $user->id ])
            ->each(function (Order $order) {
                OrderRecipient::factory()->create(['order_id' => $order->id]);
            });
        /** @var Order $order */
        $order = $orders->random();

        $res = $this->getJson(route('api.v1.mobile.user.order', [ 'order' => $order->id ]))
            ->assertOk();

        $orderData = $res->json('data');

        self::assertEquals($order->id, $orderData['id']);
    }

    public function test_it_returns_user_order_recipient_data_with_recipient_is_me_true(): void
    {
        $user = $this->loginAsUser();
        /** @var Order $order */
        $order = Order::factory()->create([ 'user_id' => $user->id ]);
        $order->createClient();

        OrderRecipient::factory()->create([
            'order_id' => $order->id,
            'recipient_is_me' => true,
            'name' => null,
            'surname' => null,
            'phone' => null,
            'email' => null,
        ]);

        $res = $this->getJson(route('api.v1.mobile.user.order', [ 'order' => $order->id ]))
            ->assertOk();

        $recipientData = $res->json('data.recipient');

        self::assertTrue($recipientData['recipient_is_me']);
        self::assertEquals($user->name, $recipientData['name']);
        self::assertEquals($user->surname, $recipientData['surname']);
        self::assertEquals($user->phone, $recipientData['phone']);
        self::assertEquals($user->email, $recipientData['email']);
    }

    public function test_it_returns_order_possibilities(): void
    {
        $user = $this->loginAsUser();
        /** @var Order $order */
        $order = Order::factory()->create([ 'user_id' => $user->id ]);
        OrderRecipient::factory()->create(['order_id' => $order->id]);

        // new status
        $res = $this->getJson(route('api.v1.mobile.user.order', [ 'order' => $order->id ]));

        $orderData = $res->json('data');

        self::assertFalse($orderData['can_be_reviewed']);
        self::assertTrue($orderData['can_be_cancelled']);

        // payed status
        $payedStatus = OrderStatus::find(OrderStatus::PAID);
        $order->changeStatus($payedStatus)->save();

        $res = $this->getJson(route('api.v1.mobile.user.order', [ 'order' => $order->id ]));

        $orderData = $res->json('data');

        self::assertFalse($orderData['can_be_reviewed']);
        self::assertTrue($orderData['can_be_cancelled']);

        // ready status
        $payedStatus = OrderStatus::find(OrderStatus::READY);
        $order->changeStatus($payedStatus)->save();

        $res = $this->getJson(route('api.v1.mobile.user.order', [ 'order' => $order->id ]));

        $orderData = $res->json('data');

        self::assertFalse($orderData['can_be_reviewed']);
        self::assertFalse($orderData['can_be_cancelled']);

        // done status
        $payedStatus = OrderStatus::find(OrderStatus::DONE);
        $order->changeStatus($payedStatus)->save();

        $res = $this->getJson(route('api.v1.mobile.user.order', [ 'order' => $order->id ]));

        $orderData = $res->json('data');

        self::assertTrue($orderData['can_be_reviewed']);
        self::assertFalse($orderData['can_be_cancelled']);
    }

    public function test_order_info_contains_status_history(): void
    {
        $user = $this->loginAsUser();
        /** @var Order $order */
        $order = Order::factory()->create([ 'user_id' => $user->id ]);
        OrderRecipient::factory()->create(['order_id' => $order->id]);
        $date = Carbon::now()->subDays(10);
        $order->statusHistory()->attach(OrderStatus::newStatus(), [ 'created_at' => $date ]);

        Carbon::setTestNow($date->addDay());
        $order->changeStatus(OrderStatus::paidStatus())->save();

        Carbon::setTestNow($date->addDay());
        $order->changeStatus(OrderStatus::readyStatus())->save();

        $res = $this->getJson(route('api.v1.mobile.user.order', [ 'order' => $order->id ]))
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'status_history' => [
                        '*' => [
                            'id',
                            'name',
                            'color',
                            'created_at',
                        ],
                    ],
                ],
            ]);

        $statusHistory = $res->json('data.status_history');

        self::assertCount(OrderStatus::statusesSequence()->count(), $statusHistory);
        self::assertNull($statusHistory[0]['created_at']);
        self::assertNull($statusHistory[1]['created_at']);
        self::assertNotNull($statusHistory[2]['created_at']);
        self::assertNotNull($statusHistory[3]['created_at']);
        self::assertNotNull($statusHistory[4]['created_at']);
    }

    public function test_it_returns_user_order_full_info_with_bonus_payment(): void
    {
        $user = $this->loginAsUser();
        $order = $this->getOrder($user->id);

        $res = $this->getJson(route('api.v1.mobile.user.order', [ 'order' => $order->id ]))
            ->assertOk();

        $orderData = $res->json('data');

        self::assertEquals($order->id, $orderData['id']);
        self::assertEquals(200, $orderData['used_bonuses']);
        self::assertEquals(500, $orderData['delivery_cost']);
        self::assertEquals(400, $orderData['discount']);
        self::assertEquals(2000, $orderData['sum']);
        self::assertEquals(1900, $orderData['total']);
    }

    private function getOrdersResponseStructure(): array
    {
        return [
            'data' => [
                'orders',
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'links' => [
                        '*' => [
                            'url',
                            'label',
                            'active',
                        ]
                    ],
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
            ],
            'success',
        ];
    }

    private function getOrder(int $userId): Order
    {
        /** @var Delivery $delivery */
        $delivery = Delivery::factory()->create([ 'driver' => SdekCourier::KEY ]);

        /** @var Provider $provider */
        $provider = Provider::factory()->create();

        /** @var Order $order */
        $order = Order::factory()->create([
            'user_id' => $userId,
            'delivery_id' => $delivery->id,
            'provider_id' => $provider->id,
        ]);

        OrderDeliveryInformation::factory()->create([ 'order_id' => $order->id, 'delivery_cost' => 500 ]);
        OrderRecipient::factory()->create([ 'order_id' => $order->id ]);

        /** @var Product $product */
        $product = Product::factory()->create([ 'cost' => 1000, 'cost_discount' => 800, 'bonus' => 150 ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'price' => $product->cost,
            'purchase_price' => $product->cost_discount,
            'quantity' => 2,
        ]);

        $order->user
            ->inviterBonusHistory()
            ->create([
                'type' => BonusHistoryType::USE,
                'order_id' => $order->id,
                'bonus' => 200,
            ]);

        $order = Order::find($order->id);

        return $order;
    }
}
