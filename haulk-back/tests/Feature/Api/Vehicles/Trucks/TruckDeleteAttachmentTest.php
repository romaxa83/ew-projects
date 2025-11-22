<?php

namespace Tests\Feature\Api\Vehicles\Trucks;

use App\Models\Users\User;
use App\Models\Vehicles\Vehicle;
use App\Models\Vehicles\Truck;
use Tests\Feature\Api\Vehicles\VehicleDeleteAttachmentTest;

class TruckDeleteAttachmentTest extends VehicleDeleteAttachmentTest
{
    protected string $routeName = 'trucks.delete-attachment';

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
