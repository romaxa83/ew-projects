<?php


namespace Tests\Feature\Modules\Orders;


use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Contracts\SettingsInterface;
use WezomCms\Core\Models\Administrator;
use WezomCms\Orders\Cart\Storage\DatabaseStorage;
use WezomCms\Orders\Contracts\CartInterface;
use WezomCms\Orders\Drivers\Delivery\SdekCourier;
use WezomCms\Orders\Models\Delivery;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderDeliveryInformation;
use WezomCms\Orders\Models\OrderItem;
use WezomCms\Orders\Models\OrderRecipient;
use WezomCms\Orders\Models\OrderStatus;
use WezomCms\Orders\Models\Payment;
use WezomCms\Providers\Models\Provider;
use WezomCms\Providers\Types\ProviderStatus;
use WezomCms\Users\Enums\BonusHistoryType;
use WezomCms\Users\Models\BonusHistory;
use WezomCms\Users\Models\User;

class BonusTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_creates_use_bonus_history_on_using_user_bonuses(): void
    {
        $user = $this->loginAsUser();

        // add start bonuses
        BonusHistory::factory()->create([
            'inviter_id' => $user->id,
            'type' => BonusHistoryType::ADJUSTMENT_PLUS,
            'bonus' => 250,
        ]);
        $data = $this->getRequestData();

        /** @var DatabaseStorage $cart */
        $cart = app(CartInterface::class);
        $cart->getMainCart()->save();
        /** @var Provider $provider */
        $provider = Provider::factory()->create([
            'status' => ProviderStatus::MODERATED,
            'active' => true,
            'admin_id' => Administrator::factory()->create([ 'super_admin' => false ])->id,
        ]);
        /** @var Product $product */
        $product = Product::factory()->create([ 'cost' => 250, 'cost_discount' => 0.0, 'provider_id' => $provider->admin_id, ]);
        $cart->add($product, 2);

        Event::fake();

        $res = $this->postJson(route('api.v1.mobile.checkout.create-order'), $data)
            ->assertStatus(200)
            ->assertJsonStructure($this->getExpectedJsonStructure());

        $order = $res->json('data.orders.0');

        $dbOrder = Order::first();

        self::assertEquals($data['use_bonus'], $dbOrder->usedBonuses());

        $this->assertDatabaseHas(
            BonusHistory::TABLE,
            [
                'inviter_id' => $user->id,
                'order_id' => $order['id'],
                'referral_id' => null,
                'type' => BonusHistoryType::USE,
                'bonus' => $data['use_bonus'],
            ]
        );

        $user->refresh();
        self::assertEquals(150, $user->bonus);
    }

    public function test_it_creates_use_bonus_history_for_multiple_orders_on_using_user_bonuses(): void
    {
        $user = $this->loginAsUser();

        // add start bonuses
        BonusHistory::factory()->create([
            'inviter_id' => $user->id,
            'type' => BonusHistoryType::ADJUSTMENT_PLUS,
            'bonus' => 350,
        ]);
        $user->updateBonusSum();
        $data = $this->getRequestData();
        $data['use_bonus'] = 300;
        $user->refresh();

        /** @var DatabaseStorage $cart */
        $cart = app(CartInterface::class);
        $cart->getMainCart()->save();
        /** @var Provider $provider1 */
        $provider1 = Provider::factory()->create([
            'status' => ProviderStatus::MODERATED,
            'active' => true,
            'admin_id' => Administrator::factory()->create([ 'super_admin' => false ])->id,
        ]);
        /** @var Product $product1 */
        $product1 = Product::factory()->create([ 'cost' => 250, 'cost_discount' => 0.0, 'provider_id' => $provider1->admin_id, ]);
        /** @var Provider $provider2 */
        $provider2 = Provider::factory()->create([
            'status' => ProviderStatus::MODERATED,
            'active' => true,
            'admin_id' => Administrator::factory()->create([ 'super_admin' => false ])->id,
        ]);
        /** @var Product $product2 */
        $product2 = Product::factory()->create([ 'cost' => 500, 'cost_discount' => 400, 'provider_id' => $provider2->admin_id, ]);
        $cart->add($product1, 1);
        $cart->add($product2, 2);

        Event::fake();

        $res = $this->postJson(route('api.v1.mobile.checkout.create-order'), $data)
            ->assertStatus(200);

        $orders = $res->json('data.orders');

        $dbOrder1 = Order::find($orders[0]['id']);
        $dbOrder2 = Order::find($orders[1]['id']);

        self::assertEquals(250, $dbOrder1->usedBonuses());
        self::assertEquals(50, $dbOrder2->usedBonuses());

        $this->assertDatabaseHas(
            BonusHistory::TABLE,
            [
                'inviter_id' => $user->id,
                'order_id' => $orders[0]['id'],
                'referral_id' => null,
                'type' => BonusHistoryType::USE,
                'bonus' => 250,
            ]
        );

        $this->assertDatabaseHas(
            BonusHistory::TABLE,
            [
                'inviter_id' => $user->id,
                'order_id' => $orders[1]['id'],
                'referral_id' => null,
                'type' => BonusHistoryType::USE,
                'bonus' => 50,
            ]
        );

        $user->refresh();
        self::assertEquals(50, $user->bonus);
    }

    public function test_it_creates_accrual_bonus_history_only_in_bonus_limit_settings(): void
    {
        /** @var User $inviter */
        $inviter = User::factory()->create();
        $user = $this->loginAsUser();
        $user->ref_id = $inviter->id;
        $user->save();

        /** @var Delivery $delivery */
        $delivery = Delivery::factory()->create(['driver' => SdekCourier::KEY]);
        /** @var Payment $payment */
        $payment = Payment::factory()->create();

        $data = [
            'delivery_id' => $delivery->id,
            'payment_id' => $payment->id,
            'delivery_data' => [
                'region_code' => 299,
                'city_code' => 4756,
                'postal_code' => '050054',
                'address' => 'Ул. Какая-то, 28',
                'tariff_code' => current(config('cms.orders.delivery-and-payment.sdek.tariffs', [])),
            ],
            'recipient' => [ 'recipient_is_me' => true ],
        ];

        /** @var DatabaseStorage $cart */
        $cart = app(CartInterface::class);
        $cart->getMainCart()->save();
        /** @var Provider $provider */
        $provider = Provider::factory()->create([
            'status' => ProviderStatus::MODERATED,
            'active' => true,
            'admin_id' => Administrator::factory()->create([ 'super_admin' => false ])->id,
        ]);
        /** @var Product $product */
        $product = Product::factory()->create([ 'cost' => 500, 'bonus' => 100, 'provider_id' => $provider->admin_id, ]);
        $cart->add($product, 2);

        $settings = app(SettingsInterface::class);
        $settings->set('referrals.site.referral_bonus_limit', 2);

        // add start bonuses
        BonusHistory::factory()
            ->count(2)
            ->create([
                'inviter_id' => $inviter->id,
                'referral_id' => $user->id,
                'bonus' => 150,
            ]);
        $inviter->updateBonusSum();

        Event::fake();

        $res = $this->postJson(route('api.v1.mobile.checkout.create-order'), $data)
            ->assertStatus(200);

        $this->assertDatabaseMissing(
            BonusHistory::TABLE,
            [
                'inviter_id' => $inviter->id,
                'order_id' => $res->json('data.orders.0.id'),
                'referral_id' => $user->id,
                'type' => BonusHistoryType::ACCRUAL,
            ]
        );

        $inviter->refresh();

        self::assertEquals(300, $inviter->bonus);
    }

    public function test_it_returns_validation_errors_on_use_more_bonuses_than_user_has(): void
    {
        $user = $this->loginAsUser();
        $user->bonus = 200;
        $user->save();

        /** @var Delivery $delivery */
        $delivery = Delivery::factory()->create(['driver' => SdekCourier::KEY]);
        /** @var Payment $payment */
        $payment = Payment::factory()->create();

        $data = [
            'delivery_id' => $delivery->id,
            'payment_id' => $payment->id,
            'delivery_data' => [
                'region_code' => 299,
                'city_code' => 4756,
                'postal_code' => '050054',
                'address' => 'Ул. Какая-то, 28',
                'tariff_code' => current(config('cms.orders.delivery-and-payment.sdek.tariffs', [])),
            ],
            'use_bonus' => 300,
            'recipient' => [ 'recipient_is_me' => true ],
        ];

        Event::fake();

        $this->postJson(route('api.v1.mobile.checkout.create-order'), $data)
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'data' => __('validation.max.numeric', [
                    'attribute' => __('cms-users::site.referrals.Use bonus'),
                    'max' => $user->bonus,
                ]),
            ]);
    }

    public function test_it_creates_accrual_bonus_history_on_change_order_status_to_done(): void
    {
        /** @var User $inviter */
        $inviter = User::factory()->create();

        $order = $this->getOrder();
        $order->user->ref_id = $inviter->id;
        $order->user->save();

        $this->assertDatabaseMissing(
            BonusHistory::TABLE,
            [
                'inviter_id' => $inviter->id,
                'order_id' => $order->id,
                'referral_id' => $order->user->id,
                'type' => BonusHistoryType::ACCRUAL,
                'bonus' => 300,
            ]
        );

        $order->status_id = OrderStatus::DONE;
        $order->save();

        $this->assertDatabaseHas(
            BonusHistory::TABLE,
            [
                'inviter_id' => $inviter->id,
                'order_id' => $order->id,
                'referral_id' => $order->user->id,
                'type' => BonusHistoryType::ACCRUAL,
                'bonus' => 300,
            ]
        );

        $inviter->refresh();
        self::assertEquals(300, $inviter->bonus);
    }

    private function getOrder(): Order
    {
        /** @var Delivery $delivery */
        $delivery = Delivery::factory()->create([ 'driver' => SdekCourier::KEY ]);

        /** @var Provider $provider */
        $provider = Provider::factory()->create();

        /** @var Order $order */
        $order = Order::factory()->create([
            'delivery_id' => $delivery->id,
            'provider_id' => $provider->id,
        ]);

        OrderDeliveryInformation::factory()->create([ 'order_id' => $order->id ]);
        OrderRecipient::factory()->create([ 'order_id' => $order->id ]);

        /** @var Product $product */
        $product = Product::factory()->create([ 'cost' => 1000, 'bonus' => 150 ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $order = Order::find($order->id);

        return $order;
    }

    private function getRequestData(): array
    {
        /** @var Delivery $delivery */
        $delivery = Delivery::factory()->create(['driver' => SdekCourier::KEY]);
        /** @var Payment $payment */
        $payment = Payment::factory()->create();

        return [
            'delivery_id' => $delivery->id,
            'payment_id' => $payment->id,
            'delivery_data' => [
                'region_code' => 299,
                'city_code' => 4756,
                'postal_code' => '050054',
                'address' => 'Ул. Какая-то, 28',
                'tariff_code' => current(config('cms.orders.delivery-and-payment.sdek.tariffs', [])),
            ],
            'recipient' => ['recipient_is_me' => true],
            'use_bonus' => 100,
        ];
    }

    private function getExpectedJsonStructure(): array
    {
        return [
            'success',
            'data' => [
                'id',
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
}
