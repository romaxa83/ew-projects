<?php

namespace Tests\Feature\Api\BodyShop\Vehicles\Trucks;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Users\User;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use Tests\Feature\Api\Vehicles\VehicleUpdateTest;
use Tests\Helpers\Traits\UserFactoryHelper;

class TruckUpdateTest extends VehicleUpdateTest
{
    use UserFactoryHelper;

    protected string $routeName = 'body-shop.trucks.update';

    protected string $tableName = Truck::TABLE_NAME;

    protected array $requestData = [];

    protected function getVehicle(array $attributes = []): Vehicle
    {
        return factory(Truck::class)->create($attributes + ['carrier_id' => null]);
    }

    protected function getRequestData(): array
    {
        if (empty($this->requestData)) {
            $this->requestData = [
                'vin' => 'DFDFDF3234234',
                'unit_number' => 'df763',
                'make' => 'Audi',
                'model' => 'A3',
                'year' => '2020',
                'type' => Vehicle::VEHICLE_TYPE_BOAT,
                'license_plate' => 'SD34343',
                'notes' => 'test notes',
                'owner_id' => (factory(VehicleOwner::class)->create())->id,
                'color' => 'red',
            ];
        }

        return $this->requestData;
    }

    protected function getComparingDBData(): array
    {
        $data = parent::getComparingDBData();

        if (isset($data['owner_id'])) {
            $data['customer_id'] = $data['owner_id'];
            unset($data['owner_id']);
        }

        return $data;
    }

    protected function loginAsPermittedUser(): User
    {
        return $this->loginAsBodyShopSuperAdmin();
    }

    protected function loginAsNotPermittedUser(): User
    {
        return $this->loginAsCarrierSuperAdmin();
    }

    public function test_it_update_for_company(): void
    {
        $this->assertDatabaseMissing($this->tableName, $this->getComparingDBData());

        $this->loginAsPermittedUser();

        $vehicle = factory(Truck::class)->create();

        $this->postJson(route($this->routeName, $vehicle), $this->getRequestData())
            ->assertForbidden();

        $this->assertDatabaseMissing($this->tableName, $this->getComparingDBData());
    }
}
