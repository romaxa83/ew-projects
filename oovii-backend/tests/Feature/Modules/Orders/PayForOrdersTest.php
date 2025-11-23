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
use WezomCms\Orders\Cart\Storage\DatabaseStorage;
use WezomCms\Orders\Contracts\CartInterface;
use WezomCms\Orders\Drivers\Delivery\SdekCourier;
use WezomCms\Orders\Drivers\Payment\Bonus;
use WezomCms\Orders\Drivers\Payment\PayBox;
use WezomCms\Orders\Enums\PayedModes;
use WezomCms\Orders\Events\CreatedOrders;
use WezomCms\Orders\Models\Delivery;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderDeliveryInformation;
use WezomCms\Orders\Models\OrderItem;
use WezomCms\Orders\Models\OrderPaymentInformation;
use WezomCms\Orders\Models\OrderRecipient;
use WezomCms\Orders\Models\OrderStatus;
use WezomCms\Orders\Models\Payment;
use WezomCms\Orders\Notifications\CreatedOrderNotification;
use WezomCms\Orders\Notifications\UserCreatedOrdersNotification;
use WezomCms\Providers\Models\Provider;
use WezomCms\Providers\Types\ProviderStatus;
use WezomCms\Users\Enums\BonusHistoryType;
use WezomCms\Users\Models\BonusHistory;
use WezomCms\Users\Models\User;

class PayForOrdersTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

//    public function test_it_can_create_new_order(): void
//    {
//        $user = $this->loginAsUser();
//        $order1 = $this->prepareOrder($user, PayBox::KEY);
//        $order2 = $this->prepareOrder($user, PayBox::KEY);
//
//        $paymentInformation = OrderPaymentInformation::create();
//        $order1->paymentInformation()->associate($paymentInformation);
//        $order1->save();
//        $order2->paymentInformation()->associate($paymentInformation);
//        $order2->save();
//
//        $data = [
//            'payment_data' => [1],
//        ];
//
//        $res = $this->postJson(
//            route('api.v1.mobile.checkout.order-payment', [ 'paymentInfo' => $paymentInformation->id ]),
//            $data,
//        );
//
//        /*dd($res->json('data'));
//
//        dd($paymentInformation->orders);*/
//    }

    private function prepareOrder(User $user, ?string $paymentDriver = null): Order
    {
        /** @var Delivery $delivery */
        $delivery = Delivery::factory()->create([ 'driver' => SdekCourier::KEY ]);

        /** @var Payment $payment */
        $payment = Payment::factory()->create([ 'driver' => $paymentDriver ]);

        /** @var Provider $provider */
        $provider = Provider::factory()->create([
            'status' => ProviderStatus::MODERATED,
            'active' => true,
            'admin_id' => Administrator::factory()->create([ 'super_admin' => false ])->id,
        ]);

        /** @var Product $product1 */
        $product1 = Product::factory()->create([
            'cost' => 500,
            'cost_discount' => 450,
            'provider_id' => $provider->admin_id,
        ]);
        /** @var Product $product2 */
        $product2 = Product::factory()->create([
            'cost' => 200,
            'cost_discount' => 0.0,
            'provider_id' => $provider->admin_id,
        ]);

        /** @var Order $order */
        $order = Order::factory()->create([
            'delivery_id' => $delivery->id,
            'payment_id' => $payment->id,
            'provider_id' => $provider->id,
            'user_id' => $user->id,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'price' => $product1->oldPriceForPurchase() ?? $product1->priceForPurchase(),
            'purchase_price' => $product1->priceForPurchase(),
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'price' => $product2->oldPriceForPurchase() ?? $product2->priceForPurchase(),
            'purchase_price' => $product2->priceForPurchase(),
        ]);

        return $order;
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
        ];
    }

    private function getExpectedJsonStructure(): array
    {
        return [
            'success',
            'data' => [
                '*' => [
                    'id',
                    'payed',
                    'paymentInfo' => [
                        'id',
                        'payment_data',
                    ],
                ],
            ],
        ];
    }
}
