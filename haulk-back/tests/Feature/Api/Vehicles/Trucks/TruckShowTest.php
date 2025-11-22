<?php

namespace Tests\Feature\Api\Vehicles\Trucks;

use App\Models\Users\User;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use Tests\Feature\Api\Vehicles\VehicleShowTest;
use Tests\Helpers\Traits\UserFactoryHelper;

class TruckShowTest extends VehicleShowTest
{
    use UserFactoryHelper;

    protected string $routeName = 'trucks.show';

    protected function getVehicle(array $attributes = []): Vehicle
    {
        $attributes = array_merge(
            [
                'owner_id' => $this->ownerFactory()->id,
                'driver_id' => $this->driverFactory()->id,
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
            ],
            'driver' => [
                'id',
                'first_name',
                'last_name',
            ],
            'hasRelatedOpenOrders',
            'hasRelatedDeletedOrders',
            'color',
        ];
    }

    protected function loginAsPermittedUser(): User
    {
        return $this->loginAsCarrierSuperAdmin();
    }

    protected function loginAsNotPermittedUser(): User
    {
        return $this->loginAsBodyShopAdmin();
    }

    public function test_it_show_for_accountant(): void
    {
        $vehicle = $this->getVehicle();

        $this->loginAsCarrierAccountant();

        $this->getJson(route($this->routeName, $vehicle))
            ->assertOk()
            ->assertJsonStructure(['data' => $this->getResponseFields()]);
    }

    public function test_it_show_for_dispatcher(): void
    {
        $vehicle = $this->getVehicle();

        $this->loginAsCarrierDispatcher();

        $this->getJson(route($this->routeName, $vehicle))
            ->assertOk()
            ->assertJsonStructure(['data' => $this->getResponseFields()]);
    }

    public function test_isRegistrationDocumentExpires_field(): void
    {
        $this->loginAsPermittedUser();
        $truck = factory(Truck::class)->create();

        $response = $this->getJson(route($this->routeName, $truck))
            ->assertOk();

        $this->assertFalse($response['data']['isRegistrationDocumentExpires']);

        $truck->registration_expiration_date = now();
        $truck->save();

        $response = $this->getJson(route($this->routeName, $truck))
            ->assertOk();

        $this->assertTrue($response['data']['isRegistrationDocumentExpires']);

        $truck->registration_expiration_date = now()->addDays(16);
        $truck->save();

        $response = $this->getJson(route($this->routeName, $truck))
            ->assertOk();

        $this->assertFalse($response['data']['isRegistrationDocumentExpires']);
    }

    public function test_isInspectionDocumentExpires_field(): void
    {
        $this->loginAsPermittedUser();
        $truck = factory(Truck::class)->create();

        $response = $this->getJson(route($this->routeName, $truck))
            ->assertOk();

        $this->assertFalse($response['data']['isInspectionDocumentExpires']);

        $truck->inspection_expiration_date = now();
        $truck->save();

        $response = $this->getJson(route($this->routeName, $truck))
            ->assertOk();

        $this->assertTrue($response['data']['isInspectionDocumentExpires']);

        $truck->inspection_expiration_date = now()->addDays(20);
        $truck->save();

        $response = $this->getJson(route($this->routeName, $truck))
            ->assertOk();

        $this->assertFalse($response['data']['isInspectionDocumentExpires']);
    }
}
