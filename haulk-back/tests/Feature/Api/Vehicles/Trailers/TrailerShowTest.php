<?php

namespace Tests\Feature\Api\Vehicles\Trailers;

use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Vehicle;
use Tests\Feature\Api\Vehicles\VehicleShowTest;
use Tests\Helpers\Traits\UserFactoryHelper;

class TrailerShowTest extends VehicleShowTest
{
    use UserFactoryHelper;

    protected string $routeName = 'trailers.show';

    protected function getVehicle(array $attributes = []): Vehicle
    {
        $attributes = array_merge(
            [
                'owner_id' => $this->ownerFactory()->id,
                'driver_id' => $this->driverFactory()->id,
            ],
            $attributes
        );
        return factory(Trailer::class)->create($attributes);
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
        $trailer = factory(Trailer::class)->create();

        $response = $this->getJson(route($this->routeName, $trailer))
            ->assertOk();

        $this->assertFalse($response['data']['isRegistrationDocumentExpires']);

        $trailer->registration_expiration_date = now();
        $trailer->save();

        $response = $this->getJson(route($this->routeName, $trailer))
            ->assertOk();

        $this->assertTrue($response['data']['isRegistrationDocumentExpires']);

        $trailer->registration_expiration_date = now()->addDays(16);
        $trailer->save();

        $response = $this->getJson(route($this->routeName, $trailer))
            ->assertOk();

        $this->assertFalse($response['data']['isRegistrationDocumentExpires']);
    }

    public function test_isInspectionDocumentExpires_field(): void
    {
        $this->loginAsPermittedUser();
        $trailer = factory(Trailer::class)->create();

        $response = $this->getJson(route($this->routeName, $trailer))
            ->assertOk();

        $this->assertFalse($response['data']['isInspectionDocumentExpires']);

        $trailer->inspection_expiration_date = now();
        $trailer->save();

        $response = $this->getJson(route($this->routeName, $trailer))
            ->assertOk();

        $this->assertTrue($response['data']['isInspectionDocumentExpires']);

        $trailer->inspection_expiration_date = now()->addDays(20);
        $trailer->save();

        $response = $this->getJson(route($this->routeName, $trailer))
            ->assertOk();

        $this->assertFalse($response['data']['isInspectionDocumentExpires']);
    }
}
