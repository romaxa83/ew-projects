<?php


namespace Api\Users\Users;


use App\Broadcasting\Events\User\DeactivateUserBroadcast;
use App\Broadcasting\Events\User\DeleteUserBroadcast;
use App\Models\Orders\Order;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class ManagerSafeDeleteTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;
    use OrderFactoryHelper;

    /**
     * @param string $role
     * @dataProvider managerRolesProvider
     */
    public function test_cant_delete_manager_with_assigned_orders(string $role): void
    {
        $this->loginAsCarrierSuperAdmin();

        $dispatcher = $this->userFactory($role);
        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $this->orderFactory(
            [
                'status' => Order::STATUS_NEW,
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
            ]
        );

        $this->putJson(route('users.change-status', $dispatcher))
            ->assertOk();

        $this->deleteJson(route('users.destroy', $dispatcher))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(
            User::TABLE_NAME,
            [
                'id' => $dispatcher->id,
                'email' => $dispatcher->email,
            ]
        );
    }

    /**
     * @param string $role
     * @dataProvider managerRolesProvider
     */
    public function test_cant_delete_manager_with_pickedup_orders(string $role): void
    {
        $this->loginAsCarrierSuperAdmin();

        $dispatcher = $this->userFactory($role);
        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $this->orderFactory(
            [
                'status' => Order::STATUS_PICKED_UP,
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
            ]
        );

        $this->putJson(route('users.change-status', $dispatcher))
            ->assertOk();

        $this->deleteJson(route('users.destroy', $dispatcher))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(
            User::TABLE_NAME,
            [
                'id' => $dispatcher->id,
                'email' => $dispatcher->email,
            ]
        );
    }

    /**
     * @param string $role
     * @dataProvider managerRolesProvider
     */
    public function test_cant_delete_manager_with_delivered_not_paid_orders(string $role): void
    {
        $this->loginAsCarrierSuperAdmin();

        $dispatcher = $this->userFactory($role);
        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $this->orderFactory(
            [
                'status' => Order::STATUS_DELIVERED,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id,
                'is_billed' => false,
                'is_paid' => false,
            ]
        );

        $this->putJson(route('users.change-status', $dispatcher))
            ->assertOk();

        $this->deleteJson(route('users.destroy', $dispatcher))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(
            User::TABLE_NAME,
            [
                'id' => $dispatcher->id,
                'email' => $dispatcher->email,
            ]
        );
    }

    /**
     * @param string $role
     * @dataProvider managerRolesProvider
     */
    public function test_can_delete_manager_with_delivered_paid_orders(string $role): void
    {
        $this->loginAsCarrierSuperAdmin();

        $dispatcher = $this->userFactory($role);
        $driver = $this->driverFactory();

        $this->orderFactory(
            [
                'status' => Order::STATUS_DELIVERED,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id,
                'is_billed' => false,
                'is_paid' => true,
            ]
        );

        Event::fake([
            DeactivateUserBroadcast::class,
            DeleteUserBroadcast::class
        ]);

        $this->putJson(route('users.change-status', $dispatcher))
            ->assertStatus(Response::HTTP_OK);

        Event::assertDispatched(DeactivateUserBroadcast::class);

        $this->deleteJson(route('users.destroy', $dispatcher))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        Event::assertDispatched(DeleteUserBroadcast::class);

        $this->assertDatabaseMissing(
            User::TABLE_NAME,
            [
                'id' => $dispatcher->id,
                'email' => $dispatcher->email,
            ]
        );
    }

    /**
     * @param string $role
     * @dataProvider managerRolesProvider
     */
    public function test_cant_delete_manager_with_paid_orders_and_attached_drivers(string $role): void
    {
        $this->loginAsCarrierSuperAdmin();

        $dispatcher = $this->userFactory($role);
        $driver = $this->driverFactory(
            [
                'owner_id' => $dispatcher->id
            ]
        );

        $this->orderFactory(
            [
                'status' => Order::STATUS_DELIVERED,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id,
                'is_billed' => false,
                'is_paid' => true,
            ]
        );

        $this->putJson(route('users.change-status', $dispatcher))
            ->assertOk();

        $this->deleteJson(route('users.destroy', $dispatcher))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(
            User::TABLE_NAME,
            [
                'id' => $dispatcher->id,
                'email' => $dispatcher->email,
            ]
        );
    }

    public function managerRolesProvider(): array
    {
        return [
            [User::ADMIN_ROLE],
            [User::DISPATCHER_ROLE],
        ];
    }
}
