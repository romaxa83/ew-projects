<?php

namespace Tests\Feature\Modules\Orders;


use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Models\Administrator;
use WezomCms\Orders\Cart\Storage\DatabaseStorage;
use WezomCms\Orders\Contracts\CartInterface;
use WezomCms\Orders\Contracts\CartItemInterface;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderItem;
use WezomCms\Providers\Models\Provider;
use WezomCms\Providers\Types\ProviderStatus;

class OrderItemsTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    public function test_it_calculates_dimensions_of_cart_item(): void
    {
        /** @var CartInterface $cart */
        $cart = $this->prepareCart();

        $cartItems = $cart['cart']->content();
        /** @var CartItemInterface $firstItem */
        $firstItem = $cartItems->shift();

        self::assertEquals(1000, $firstItem->getWeight());
        self::assertEquals(20, $firstItem->getLength());
        self::assertEquals(25, $firstItem->getWidth());
        self::assertEquals(40, $firstItem->getHeight());

        /** @var CartItemInterface $firstItem */
        $secondItem = $cartItems->shift();

        self::assertEquals(600, $secondItem->getWeight());
        self::assertEquals(45, $secondItem->getLength());
        self::assertEquals(20, $secondItem->getWidth());
        self::assertEquals(65, $secondItem->getHeight());
    }

    public function test_it_calculates_dimensions_of_order_item(): void
    {
        /** @var Product $product */
        $product = Product::factory()->create(['dimensions' => [15, 25, 50]]);

        /** @var Order $order */
        $order = Order::factory()->create();

        /** @var OrderItem $orderItem */
        $orderItem = OrderItem::factory()->create([
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => $product->cost,
            'purchase_price' => $product->cost_discount,
            'order_id' => $order->id,
        ]);

        self::assertEquals(45, $orderItem->getLength());
        self::assertEquals(25, $orderItem->getWidth());
        self::assertEquals(50, $orderItem->getHeight());
    }

    private function prepareCart(): array
    {
        /** @var DatabaseStorage $cart */
        $cart = app(CartInterface::class);

        /** @var Provider $provider */
        $provider = Provider::factory()->create([
            'status' => ProviderStatus::MODERATED,
            'active' => true,
            'admin_id' => Administrator::factory()->create([ 'super_admin' => false ])->id,
        ]);

        $cart->getMainCart()->save();
        /** @var Product $product1 */
        $product1 = Product::factory()->create([
            'weight' => 500,
            'dimensions' => [10, 25, 40],
            'provider_id' => $provider->admin_id,
        ]);
        /** @var Product $product2 */
        $product2 = Product::factory()->create([
            'weight' => 200,
            'dimensions' => [15, 20, 65],
            'provider_id' => $provider->admin_id,
        ]);

        $cart->add($product1, 2);
        $cart->add($product2, 3);

        return [
            'product1' => $product1,
            'product2' => $product2,
            'cart' => $cart,
        ];
    }
}
