<?php

namespace Tests\Feature\Api\BodyShop\Vehicles\Trucks;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Users\User;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use Tests\Feature\Api\Vehicles\VehicleShowTest;
use Tests\Helpers\Traits\UserFactoryHelper;

class TruckShowTest extends VehicleShowTest
{
    use UserFactoryHelper;

    protected string $routeName = 'body-shop.trucks.show';

    protected function getVehicle(array $attributes = []): Vehicle
    {
        $attributes = array_merge(
            [
                'customer_id' => (factory(VehicleOwner::class)->create())->id,
                'carrier_id' => null,
            ],
            $attributes
        );
        return factory(Truck::class)->create($attributes);
    }

    protected function getResponseFields(): array
    {
        return [
            'id',
            'vin',
            'unit_number',
            'make',
            'model',
            'year',
            'type',
            'license_plate',
            'temporary_plate',
            'notes',
            'owner' => [
                'id',
                'first_name',
                'last_name',
                'phone',
                'email',
                'phone_extension',
            ],
            'driver',
            'company_name',
            'hasRelatedOpenOrders',
            'hasRelatedDeletedOrders',
            'color',
        ];
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
