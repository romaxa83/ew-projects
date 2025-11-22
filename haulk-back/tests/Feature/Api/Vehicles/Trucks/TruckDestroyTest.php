<?php

namespace Tests\Feature\Api\Vehicles\Trucks;

use App\Models\Users\User;
use Tests\Feature\Api\Vehicles\VehicleDestroyTest;
use App\Models\Vehicles\Vehicle;
use App\Models\Vehicles\Truck;

class TruckDestroyTest extends VehicleDestroyTest
{
    protected string $routeName = 'trucks.destroy';

    protected string $tableName = Truck::TABLE_NAME;
    protected string $orderColumnName = 'truck_id';

    protected function getVehicle(array $attributes = []): Vehicle
    {
        return factory(Truck::class)->create($attributes);
    }

    protected function loginAsPermittedUser(): User
    {
        return $this->loginAsCarrierSuperAdmin();
    }

    protected function loginAsNotPermittedUser(): User
    {
        return $this->loginAsCarrierDispatcher();
    }
}
