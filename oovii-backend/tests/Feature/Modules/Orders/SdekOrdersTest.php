<?php


namespace Tests\Feature\Modules\Orders;


use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use WezomCms\Catalog\Models\Product;
use WezomCms\Orders\Drivers\Delivery\SdekCourier;
use WezomCms\Orders\Models\Delivery;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderDeliveryInformation;
use WezomCms\Orders\Models\OrderItem;
use WezomCms\Orders\Models\OrderRecipient;
use WezomCms\Providers\Models\Provider;

class SdekOrdersTest extends TestCase
{
    use DatabaseTransactions;

    /*public function test_it_creates_sdek_order_on_change_order_status_to_ready_with_sdek_courier_delivery(): void
    {
        config([ 'cms.orders.delivery-and-payment.sdek.test' => true ]);
        $order = $this->getOrder();

        $order->status_id = OrderStatus::DONE;
        $order->save();

        self::assertNull($order->deliveryInformation->ttn);
        self::assertNull($order->deliveryInformation->uuid);

        $order->status_id = OrderStatus::READY;
        $order->save();
        $order->refresh();

        self::assertNotNull($order->deliveryInformation->ttn);
        self::assertNotNull($order->deliveryInformation->uuid);
        self::assertEquals(OrderStatus::READY, $order->status_id);
    }*/

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

        /** @var Product $product1 */
        $product1 = Product::factory()->create([
            'cost' => 1000,
            'bonus' => 150,
            'weight' => 250,
            'dimensions' => [20, 25, 30],
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'price' => $product1->cost,
            'purchase_price' => $product1->cost,
        ]);

        /** @var Product $product2 */
        $product2 = Product::factory()->create([
            'cost' => 400,
            'weight' => 150,
            'dimensions' => [5, 15, 20],
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'quantity' => 3,
            'price' => $product2->cost,
            'purchase_price' => $product2->cost,
        ]);

        $order = Order::find($order->id);

        return $order;
    }
}
