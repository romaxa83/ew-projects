<?php

namespace Tests\Feature\Api\Orders;

use App\Broadcasting\Events\Orders\DeleteOrderBroadcast;
use App\Broadcasting\Events\Orders\RestoreOrderBroadcast;
use App\Events\ModelChanged;
use App\Models\Orders\Order;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\TestCase;

class OrderDeleteTest extends TestCase
{
    use DatabaseTransactions;
    use OrderFactoryHelper;

    public function test_it_soft_delete_forbidden()
    {
        self::markTestIncomplete();
        //TODO бухгалтeр может удалить заказ?
        $this->loginAsCarrierAccountant();

        $order = $this->orderFactory();

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'deleted_at' => null,
            ]
        );

        $this->deleteJson(route('orders.destroy', $order->id))
            ->assertForbidden();

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'deleted_at' => null,
            ]
        );
    }

    public function test_it_soft_delete_success()
    {
        $this->loginAsCarrierDispatcher();

        $order = $this->orderFactory();

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'deleted_at' => null,
            ]
        );

        Event::fake([
            ModelChanged::class,
            DeleteOrderBroadcast::class
        ]);

        $this->deleteJson(route('orders.destroy', $order->id))
            ->assertNoContent();

        Event::assertDispatched(ModelChanged::class, 1);
        Event::assertDispatched(DeleteOrderBroadcast::class, 1);

        $this->assertDatabaseMissing(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'deleted_at' => null,
            ]
        );
    }

    public function test_it_delete_order_permanent_success()
    {
        $this->loginAsCarrierSuperAdmin();

        $order = $this->orderFactory();

        Event::fake([
            ModelChanged::class,
            DeleteOrderBroadcast::class
        ]);

        $this->deleteJson(route('orders.delete-permanently', ['order_id' => $order->id]))
            ->assertNoContent();

        Event::assertDispatched(ModelChanged::class, 1);
        Event::assertDispatched(DeleteOrderBroadcast::class, 1);

        $this->assertDatabaseMissing(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
            ]
        );
    }

    public function test_it_delete_order_permanent_forbidden()
    {
        $this->loginAsCarrierDispatcher();

        $order = $this->orderFactory();

        Event::fake([
            ModelChanged::class,
            DeleteOrderBroadcast::class
        ]);

        $this->deleteJson(route('orders.delete-permanently', ['order_id' => $order->id]))
            ->assertForbidden();

        Event::assertNotDispatched(ModelChanged::class);
        Event::assertNotDispatched(DeleteOrderBroadcast::class);

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function test_it_restore_order_after_soft_delete_forbidden()
    {
        $this->loginAsCarrierDispatcher();

        $order = $this->orderFactory();

        $order->delete();

        $this->assertDatabaseMissing(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'deleted_at' => null,
            ]
        );

        $this->putJson(route('orders.restore', ['order_id' => $order->id]))
            ->assertForbidden();

        $this->assertDatabaseMissing(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'deleted_at' => null,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function test_it_restore_order_after_soft_delete()
    {
        $this->loginAsCarrierSuperAdmin();

        $order = $this->orderFactory();

        $order->delete();

        $this->assertDatabaseMissing(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'deleted_at' => null,
            ]
        );

        Event::fake([
            ModelChanged::class,
            RestoreOrderBroadcast::class
        ]);

        $this->putJson(route('orders.restore', ['order_id' => $order->id]))
            ->assertOk();

        Event::assertDispatched(ModelChanged::class, 1);
        Event::assertDispatched(RestoreOrderBroadcast::class, 1);

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'deleted_at' => null,
            ]
        );
    }
}
