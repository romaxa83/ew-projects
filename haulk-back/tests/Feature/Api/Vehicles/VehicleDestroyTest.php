<?php

namespace Tests\Feature\Api\Vehicles;
use App\Models\BodyShop\Orders\Order;
use App\Models\Users\User;
use App\Models\Vehicles\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

abstract class VehicleDestroyTest extends TestCase
{
    use DatabaseTransactions;

    protected string $routeName = '';

    protected string $tableName = '';

    protected string $orderColumnName = '';

    abstract protected function getVehicle(array $attributes = []): Vehicle;

    abstract protected function loginAsPermittedUser(): User;

    abstract protected function loginAsNotPermittedUser(): User;

    public function test_it_not_delete_for_unauthorized_users(): void
    {
        $this->deleteJson(route($this->routeName, $this->getVehicle()))
            ->assertUnauthorized();
    }

    public function test_it_not_delete_for_not_permitted_users(): void
    {
        $this->loginAsNotPermittedUser();

        $this->deleteJson(route($this->routeName, $this->getVehicle()))
            ->assertForbidden();
    }

    public function test_it_delete(): void
    {
        $vehicle = $this->getVehicle();

        $this->assertDatabaseHas($this->tableName, $vehicle->getAttributes());

        $this->loginAsPermittedUser();
        $this->deleteJson(route($this->routeName, $vehicle))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing($this->tableName, $vehicle->getAttributes());
    }

    public function test_it_delete_with_new_order(): void
    {
        $vehicle = $this->getVehicle();
        factory(Order::class)->create([
            $this->orderColumnName => $vehicle->id,
            'status' => Order::STATUS_NEW,
            'status_changed_at' => now(),
        ]);

        $this->assertDatabaseHas($this->tableName, $vehicle->getAttributes());

        $this->loginAsPermittedUser();
        $this->deleteJson(route($this->routeName, $vehicle))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas($this->tableName, $vehicle->getAttributes());
    }


    public function test_it_delete_with_in_process_order(): void
    {
        $vehicle = $this->getVehicle();
        factory(Order::class)->create([
            $this->orderColumnName => $vehicle->id,
            'status' => Order::STATUS_IN_PROCESS,
            'status_changed_at' => now(),
        ]);

        $this->assertDatabaseHas($this->tableName, $vehicle->getAttributes());

        $this->loginAsPermittedUser();
        $this->deleteJson(route($this->routeName, $vehicle))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas($this->tableName, $vehicle->getAttributes());
    }


    public function test_it_delete_with_finished_order_less_than_24_hours(): void
    {
        $vehicle = $this->getVehicle();
        factory(Order::class)->create([
            $this->orderColumnName => $vehicle->id,
            'status' => Order::STATUS_FINISHED,
            'status_changed_at' => now(),
        ]);

        $this->assertDatabaseHas($this->tableName, $vehicle->getAttributes());

        $this->loginAsPermittedUser();
        $this->deleteJson(route($this->routeName, $vehicle))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas($this->tableName, $vehicle->getAttributes());
    }

    public function test_it_delete_with_finished_order_more_than_24_hours(): void
    {
        $vehicle = $this->getVehicle();
        factory(Order::class)->create([
            $this->orderColumnName => $vehicle->id,
            'status' => Order::STATUS_FINISHED,
            'status_changed_at' => now()->addHours(-25),
        ]);

        $this->assertDatabaseHas($this->tableName, $vehicle->getAttributes());

        $this->loginAsPermittedUser();
        $this->deleteJson(route($this->routeName, $vehicle))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $vehicle->refresh();
        $this->assertDatabaseHas($this->tableName, $vehicle->getAttributes());
        $this->assertNotEmpty($vehicle->deleted_at);
    }

    public function test_it_delete_with_deleted_order(): void
    {
        $vehicle = $this->getVehicle();
        $order = factory(Order::class)->create([
            $this->orderColumnName => $vehicle->id,
            'status' => Order::STATUS_FINISHED,
            'status_changed_at' => now(),
        ]);
        $order->delete();

        $this->assertDatabaseHas($this->tableName, $vehicle->getAttributes());

        $this->loginAsPermittedUser();
        $this->deleteJson(route($this->routeName, $vehicle))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas($this->tableName, $vehicle->getAttributes());
    }
}
