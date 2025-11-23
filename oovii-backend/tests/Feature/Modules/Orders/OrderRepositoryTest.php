<?php

namespace Tests\Feature\Modules\Orders;


use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Orders\Drivers\Delivery\SdekCourier;
use WezomCms\Orders\Models\Delivery;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderDeliveryInformation;
use WezomCms\Orders\Repositories\OrdersRepository;

class OrderRepositoryTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    public function test_it_calculates_dimensions_of_cart_item(): void
    {
        /** @var Delivery $delivery1 */
        $delivery1 = Delivery::factory()->create(['driver' => SdekCourier::KEY]);
        /** @var Delivery $delivery2 */
        $delivery2 = Delivery::factory()->create();

        /** @var Order $order1 order without uuid */
        $order1 = Order::factory()->create([
            'delivery_id' => $delivery1->id,
        ]);
        OrderDeliveryInformation::factory()->create([
            'order_id' => $order1->id,
        ]);

        /** @var Order $order2 order with another delivery driver */
        $order2 = Order::factory()->create([
            'delivery_id' => $delivery2->id,
        ]);
        OrderDeliveryInformation::factory()->create([
            'order_id' => $order2->id,
            'uuid' => 'some-uuid',
        ]);

        /** @var Order $order3 order with uuid */
        $order3 = Order::factory()->create([
            'delivery_id' => $delivery1->id,
        ]);
        OrderDeliveryInformation::factory()->create([
            'order_id' => $order3->id,
            'uuid' => 'another-uuid',
        ]);

        $repo = resolve(OrdersRepository::class);
        $orders = $repo->getSdekOrdersWithoutTtn();

        self::assertCount(1, $orders);
        self::assertEquals($order3->id, $orders->first()->id);
    }
}
