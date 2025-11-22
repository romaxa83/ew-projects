<?php

namespace Tests\Feature\Api\BodyShop\Vehicles\Trucks;

use App\Models\Users\User;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use Tests\Feature\Api\Vehicles\VehicleDeleteAttachmentTest;

class TruckDeleteAttachmentTest extends VehicleDeleteAttachmentTest
{
    protected string $routeName = 'body-shop.trucks.delete-attachment';

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
}
