<?php


namespace Tests\Feature\Modules\Orders;


use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Catalog\Models\Product;
use WezomCms\Orders\Contracts\CartInterface;
use WezomCms\Orders\Drivers\Payment\Bonus;
use WezomCms\Orders\Drivers\Payment\PayBox;
use WezomCms\Orders\Enums\PayedModes;
use WezomCms\Orders\Models\Delivery;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderDeliveryInformation;
use WezomCms\Orders\Models\OrderItem;
use WezomCms\Orders\Models\OrderPaymentInformation;
use WezomCms\Orders\Models\OrderStatus;
use WezomCms\Orders\Models\Payment;
use WezomCms\Users\Enums\BonusHistoryType;
use WezomCms\Users\Models\BonusHistory;
use WezomCms\Users\Models\User;

class PaymentTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    public function test_it_returns_list_of_payments(): void
    {
        $user = $this->loginAsUser();
        $user->bonus = 400;
        $user->save();

        $payments = Payment::factory()
            ->count(3)
            ->state(
                new Sequence(
                    [],
                    ['driver' => PayBox::KEY],
                    ['driver' => Bonus::KEY],
                )
            )
            ->sequence(fn($sequence) => ['sort' => $sequence->index])
            ->create();
        Delivery::factory()->create(['published' => false]);

        // Creating cart
        /** @var Product $product */
        $product = Product::factory()->create(['cost' => 250, 'cost_discount' => 0.0]);

        $cartHash = sha1(microtime() . Str::random());
        config(['cms.orders.cart.hash' => $cartHash]);

        $cart = resolve(CartInterface::class);
        $cart->add($product, 1);

        // Request with enough bonus sum
        $res = $this->getJson(route('api.v1.mobile.payment-drivers'))
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(
                $this->structureResource(
                    [
                        '*' => [
                            'id',
                            'name',
                            'sort',
                            'driver',
                            'icon',
                        ]
                    ]
                )
            );

        $paymentsData = $payments
            ->sortBy(
                function (Payment $payment) {
                    return $payment->sort;
                }
            )
            ->map(
                function (Payment $payment) {
                    return $payment->only(['id', 'sort', 'driver', 'name', 'icon']);
                }
            )
            ->values()
            ->toArray();

        self::assertEquals(
            $paymentsData,
            $res->json('data'),
        );

        $cart->add($product, 1);

        // Request with no enough bonus sum
        $res = $this->getJson(route('api.v1.mobile.payment-drivers'))
            ->assertOk()
            ->assertJsonCount(2, 'data');

        self::assertCount(
            0,
            collect($res->json('data'))->filter(fn(array $payment) => $payment['driver'] === Bonus::KEY)
        );
    }

    public function test_it_set_order_paid_on_correct_request_to_result_url_from_pay_box(): void
    {
        $user = $this->loginAsUser();
        $paymentInformation = $this->prepareOrderPayment($user);

        $payBoxRequest = $this->getPayBoxRequest($paymentInformation);

        foreach ($paymentInformation->orders as $order) {
            $this->assertDatabaseMissing(
                Order::TABLE,
                [
                    'id' => $order->id,
                    'payed' => true,
                ]
            );
        }

        $this->postJson(route('api.v1.mobile.pay-box.result'), $payBoxRequest)
            ->assertOk();

        foreach ($paymentInformation->orders as $order) {
            $this->assertDatabaseHas(
                Order::TABLE,
                [
                    'id' => $order->id,
                    'payed' => true,
                    'payed_mode' => PayedModes::AUTO,
                ]
            );

            $this->assertDatabaseHas(
                'order_status_history',
                [
                    'order_id' => $order->id,
                    'status_id' => OrderStatus::PAID,
                ]
            );
        }

        $paymentInformation->refresh();

        $assertedPaymentData = Arr::only(
            $payBoxRequest,
            [
                'pg_payment_id',
                'pg_result',
                'pg_card_pan',
                'pg_salt',
                'pg_sig',
                'pg_payment_method',
                'pg_card_owner',
                'pg_card_brand',
            ]
        );

        self::assertEquals($assertedPaymentData, $paymentInformation->payment_data);
    }

    private function prepareOrderPayment(User $user): OrderPaymentInformation
    {
        $paymentInformation = OrderPaymentInformation::create();

        /** @var Product $product1 */
        $product1 = Product::factory()->create(['cost' => 1000, 'cost_discount' => 0.0]);
        /** @var Product $product2 */
        $product2 = Product::factory()->create(['cost' => 500, 'cost_discount' => 0.0]);

        /** @var Order $order1 */
        $order1 = Order::factory()->create(
            [
                'user_id' => $user->id,
                'payment_information_id' => $paymentInformation->id,
            ]
        );
        OrderDeliveryInformation::factory()->create(
            [
                'order_id' => $order1->id,
                'delivery_cost' => 400.0,
            ]
        );
        OrderItem::factory()->create(
            [
                'order_id' => $order1->id,
                'product_id' => $product1->id,
                'price' => 1000,
                'purchase_price' => 1000,
                'quantity' => 2,
            ]
        );
        /** @var Order $order2 */
        $order2 = Order::factory()->create(
            [
                'user_id' => $user->id,
                'payment_information_id' => $paymentInformation->id,
            ]
        );
        OrderDeliveryInformation::factory()->create(
            [
                'order_id' => $order2->id,
                'delivery_cost' => 250.0,
            ]
        );
        OrderItem::factory()->create(
            [
                'order_id' => $order2->id,
                'product_id' => $product2->id,
                'price' => 500,
                'purchase_price' => 500,
                'quantity' => 3,
            ]
        );

        $paymentInformation->setOrderIds()->save();

        return $paymentInformation;
    }

    private function getPayBoxRequest(OrderPaymentInformation $paymentInformation, int $correctSum = 0): array
    {
        $sum = $paymentInformation->getTotalSum() + $correctSum;

        return [
            'pg_order_id' => $paymentInformation->order_ids,
            'pg_payment_id' => '578256369',
            'pg_amount' => $sum,
            'pg_currency' => 'KZT',
            'pg_net_amount' => round($sum * 0.95, 2),
            'pg_ps_amount' => $sum,
            'pg_ps_full_amount' => $sum,
            'pg_ps_currency' => 'KZT',
            'pg_description' => '\u041e\u043f\u043b\u0430\u0442\u0430 \u0437\u0430 \u0437\u0430\u043a\u0430\u0437 \u211642',
            'pg_result' => '1',
            'pg_payment_date' => '2022-04-19 21:55:37',
            'pg_can_reject' => '0',
            'pg_user_phone' => '77777777773',
            'pg_need_phone_notification' => '0',
            'pg_user_contact_email' => 'maka@dd.dd',
            'pg_need_email_notification' => '1',
            'pg_testing_mode' => '1',
            'pg_payment_method' => 'bankcard',
            'pg_captured' => '0',
            'pg_card_pan' => '4563-96XX-XXXX-1999',
            'pg_card_exp' => '12\/24',
            'pg_card_owner' => 'Handy man',
            'pg_auth_code' => '123456',
            'pg_card_brand' => 'VI',
            'pg_salt' => 'Ud8Rtf5EMMbRslt4',
            'pg_sig' => 'ae396bc7510d19d065cce818c8700868',
        ];
    }

    public function test_it_doesnt_set_order_paid_on_request_with_no_sufficient_sum(): void
    {
        $user = $this->loginAsUser();
        $paymentInformation = $this->prepareOrderPayment($user);

        $payBoxRequest = $this->getPayBoxRequest($paymentInformation, -100);

        $this->postJson(route('api.v1.mobile.pay-box.result'), $payBoxRequest)
            ->assertOk();

        foreach ($paymentInformation->orders as $order) {
            $this->assertDatabaseMissing(
                Order::TABLE,
                [
                    'id' => $order->id,
                    'payed' => true,
                ]
            );

            $this->assertDatabaseMissing(
                'order_status_history',
                [
                    'order_id' => $order->id,
                    'status_id' => OrderStatus::PAID,
                ]
            );
        }

        $paymentInformation->refresh();

        $assertedPaymentData = Arr::only(
            $payBoxRequest,
            [
                'pg_payment_id',
                'pg_result',
                'pg_card_pan',
                'pg_salt',
                'pg_sig',
                'pg_payment_method',
                'pg_card_owner',
                'pg_card_brand',
            ]
        );

        self::assertEquals($assertedPaymentData, $paymentInformation->payment_data);
    }

    public function test_it_doesnt_set_order_paid_on_request_with_false_result(): void
    {
        $user = $this->loginAsUser();
        $paymentInformation = $this->prepareOrderPayment($user);

        $payBoxRequest = $this->getPayBoxFailedRequest($paymentInformation);

        $this->postJson(route('api.v1.mobile.pay-box.result'), $payBoxRequest)
            ->assertOk();

        foreach ($paymentInformation->orders as $order) {
            $this->assertDatabaseMissing(
                Order::TABLE,
                [
                    'id' => $order->id,
                    'payed' => true,
                ]
            );

            $this->assertDatabaseMissing(
                'order_status_history',
                [
                    'order_id' => $order->id,
                    'status_id' => OrderStatus::PAID,
                ]
            );
        }

        $paymentInformation->refresh();

        $assertedPaymentData = Arr::only(
            $payBoxRequest,
            [
                'pg_payment_id',
                'pg_result',
                'pg_card_pan',
                'pg_salt',
                'pg_sig',
                'pg_payment_method',
                'pg_card_owner',
                'pg_card_brand',
                'pg_failure_code',
                'pg_failure_description',
            ]
        );

        self::assertEquals($assertedPaymentData, $paymentInformation->payment_data);
    }

    private function getPayBoxFailedRequest(OrderPaymentInformation $paymentInformation): array
    {
        $sum = $paymentInformation->getTotalSum();

        return [
            'pg_order_id' => $paymentInformation->order_ids,
            'pg_payment_id' => '578256369',
            'pg_amount' => $sum,
            'pg_currency' => 'KZT',
            'pg_net_amount' => round($sum * 0.95, 2),
            'pg_ps_amount' => $sum,
            'pg_ps_full_amount' => $sum,
            'pg_ps_currency' => 'KZT',
            'pg_description' => '\u041e\u043f\u043b\u0430\u0442\u0430 \u0437\u0430 \u0437\u0430\u043a\u0430\u0437 \u211642',
            'pg_result' => '0',
            'pg_can_reject' => '0',
            'pg_user_phone' => '77777777773',
            'pg_need_phone_notification' => '0',
            'pg_user_contact_email' => 'maka@dd.dd',
            'pg_need_email_notification' => '1',
            'pg_testing_mode' => '1',
            'pg_payment_method' => 'bankcard',
            'pg_captured' => '0',
            'pg_card_brand' => 'VI',
            'pg_failure_code' => '5',
            'pg_failure_description' => '3DS failed',
            'pg_salt' => 'Ud8Rtf5EMMbRslt4',
            'pg_sig' => 'ae396bc7510d19d065cce818c8700868',
        ];
    }

    public function test_it_set_order_paid_on_request_to_result_url_from_pay_box_with_partly_bonus_pay(): void
    {
        $user = $this->loginAsUser();
        $paymentInformation = $this->prepareOrderPayment($user);
        $paymentSum = $paymentInformation->getTotalSum();

        $firstOrder = $paymentInformation->orders->first();
        BonusHistory::factory()->create(
            [
                'inviter_id' => $user->id,
                'type' => BonusHistoryType::USE,
                'order_id' => $firstOrder->id,
                'bonus' => 1200,
            ]
        );

        $payBoxRequest = $this->getPayBoxRequest($paymentInformation, -1200);

        foreach ($paymentInformation->orders as $order) {
            $this->assertDatabaseMissing(
                Order::TABLE,
                [
                    'id' => $order->id,
                    'payed' => true,
                ]
            );
        }

        $this->postJson(route('api.v1.mobile.pay-box.result'), $payBoxRequest)
            ->assertOk();

        foreach ($paymentInformation->orders as $order) {
            $this->assertDatabaseHas(
                Order::TABLE,
                [
                    'id' => $order->id,
                    'payed' => true,
                    'payed_mode' => PayedModes::AUTO,
                ]
            );

            $this->assertDatabaseHas(
                'order_status_history',
                [
                    'order_id' => $order->id,
                    'status_id' => OrderStatus::PAID,
                ]
            );
        }
    }
}
