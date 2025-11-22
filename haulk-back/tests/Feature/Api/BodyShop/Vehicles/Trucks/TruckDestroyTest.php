<?php

namespace Tests\Feature\Api\BodyShop\Vehicles\Trucks;

use App\Models\Users\User;
use Tests\Feature\Api\Vehicles\VehicleDestroyTest;
use App\Models\Vehicles\Vehicle;
use App\Models\Vehicles\Truck;

class TruckDestroyTest extends VehicleDestroyTest
{
    protected string $routeName = 'body-shop.trucks.destroy';

    protected string $tableName = Truck::TABLE_NAME;
    protected string $orderColumnName = 'truck_id';

    protected function getVehicle(array $attributes = []): Vehicle
    {
        return factory(Truck::class)->create($attributes + ['carrier_id' => null]);
    }

    protected function loginAsPermittedUser(): User
    {
        return $this->loginAsBodyShopSuperAdmin();
    }

    protected function loginAsNotPermittedUser(): User
    {
        return $this->loginAsCarrierSuperAdmin();
    }

    public function test_it_delete_from_company(): void
    {
        $vehicle = factory(Truck::class)->create();

        $this->assertDatabaseHas($this->tableName, $vehicle->getAttributes());

        $this->loginAsPermittedUser();
        $this->deleteJson(route($this->routeName, $vehicle))
            ->assertForbidden();

        $this->assertDatabaseHas($this->tableName, $vehicle->getAttributes());
    }
}
