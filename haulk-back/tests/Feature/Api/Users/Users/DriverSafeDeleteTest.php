<?php


namespace Api\Users\Users;


use App\Broadcasting\Events\User\DeactivateUserBroadcast;
use App\Broadcasting\Events\User\DeleteUserBroadcast;
use App\Models\Orders\Order;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\ElasticsearchClear;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\Orders\OrderESSavingHelper;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class DriverSafeDeleteTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;
    use OrderFactoryHelper;
    use ElasticsearchClear;
    use OrderESSavingHelper;

    public function test_cant_delete_driver_with_assigned_orders(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $driver = $this->driverFactory();
        $dispatcher = $this->dispatcherFactory();

        $order = $this->orderFactory(
            [
                'status' => Order::STATUS_NEW,
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
            ]
        );
        $this->makeDocuments();
        $this->putJson(route('users.change-status', $driver))
            ->assertStatus(Response::HTTP_OK);
        $this->deleteJson(route('users.destroy', $driver))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(
            User::TABLE_NAME,
            [
                'id' => $driver->id,
                'email' => $driver->email,
            ]
        );
    }

    public function test_cant_delete_driver_with_pickedup_orders(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $order = Order::factory()
            ->pickedUpStatus()
            ->create();
        $this->makeDocuments();
        $this->putJson(route('users.change-status', $order->driver))
            ->assertStatus(Response::HTTP_OK);

        $this->deleteJson(route('users.destroy', $order->driver))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(
            User::TABLE_NAME,
            [
                'id' => $order->driver_id,
            ]
        );
    }

    public function test_can_delete_driver_with_delivered_orders(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $driver = $this->driverFactory();
        $dispatcher = $this->dispatcherFactory();

        $order = $this->orderFactory(
            [
                'status' => Order::STATUS_DELIVERED,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id,
            ]
        );

        Event::fake([
            DeactivateUserBroadcast::class,
            DeleteUserBroadcast::class
        ]);

        $this->putJson(route('users.change-status', $driver))
            ->assertStatus(Response::HTTP_OK);

        Event::assertDispatched(DeactivateUserBroadcast::class);

        $this->deleteJson(route('users.destroy', $driver))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        Event::assertDispatched(DeleteUserBroadcast::class);

        $this->assertDatabaseMissing(
            User::TABLE_NAME,
            [
                'id' => $driver->id,
                'email' => $driver->email,
            ]
        );
    }
}
