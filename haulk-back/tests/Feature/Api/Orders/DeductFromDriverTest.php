<?php


namespace Api\Orders;


use App\Broadcasting\Events\Orders\OrderChangeDeductBroadcast;
use App\Events\Orders\OrderUpdateEvent;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class DeductFromDriverTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;
    use OrderFactoryHelper;

    public function test_deduct_from_driver()
    {
        $this->loginAsCarrierSuperAdmin();

        $user = $this->driverFactory();

        /**@var Order $order*/
        $order = Order::factory()->create(['driver_id' => $user->id]);
        $this->assertFalse($order->deduct_from_driver);

        Event::fake();

        $this->putJson(route('orders.change-deduct-from-driver', ['order' => $order]))->assertOk();

        Event::assertDispatched(OrderChangeDeductBroadcast::class);
        Event::assertDispatched(OrderUpdateEvent::class);

        $order->refresh();
        $this->assertTrue($order->deduct_from_driver);

        $this->putJson(route('orders.change-deduct-from-driver', ['order' => $order]))->assertOk();

        Event::assertDispatched(OrderChangeDeductBroadcast::class);
        Event::assertDispatched(OrderUpdateEvent::class);

        $order->refresh();
        $this->assertFalse($order->deduct_from_driver);
    }
}
