<?php

namespace Api\BodyShop\VehicleOwners;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VehicleOwnerShowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_show_vehicle_owner_for_unauthorized_users()
    {
        $vehicleOwner = factory(VehicleOwner::class)->create();

        $this->getJson(route('body-shop.vehicle-owners.show', $vehicleOwner))->assertUnauthorized();
    }

    public function test_it_not_show_vehicle_owner_for_not_permitted_users()
    {
        $vehicleOwner = factory(VehicleOwner::class)->create();

        $this->loginAsCarrierSuperAdmin();

        $this->getJson(route('body-shop.vehicle-owners.show', $vehicleOwner))
            ->assertForbidden();
    }

    public function test_it_show_vehicle_owner_for_permitted_users()
    {
        $vehicleOwner = factory(VehicleOwner::class)->create();

        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.vehicle-owners.show', $vehicleOwner))
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'first_name',
                'last_name',
                'email',
                'phone',
                'phone_extension',
                'phones',
                'notes',
                'attachments',
                'tags',
            ]]);

        $this->loginAsBodyShopAdmin();

        $this->getJson(route('body-shop.vehicle-owners.show', $vehicleOwner))
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'first_name',
                'last_name',
                'email',
                'phone',
                'phone_extension',
                'phones',
                'notes',
                'attachments',
                'tags',
            ]]);
    }

    public function test_it_show_related_vehicles()
    {
        $vehicleOwner = factory(VehicleOwner::class)->create();
        factory(Truck::class)->create(['customer_id' => $vehicleOwner->id]);
        factory(Truck::class)->create(['customer_id' => $vehicleOwner->id]);
        factory(Trailer::class)->create(['customer_id' => $vehicleOwner->id]);

        $this->loginAsBodyShopSuperAdmin();

        $response = $this->getJson(route('body-shop.vehicle-owners.show', $vehicleOwner))
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'first_name',
                'last_name',
                'email',
                'phone',
                'phone_extension',
                'phones',
                'notes',
                'attachments',
                'tags',
                'trucks' => [
                    '*' => [
                        'id',
                        'vin',
                        'unit_number',
                        'license_plate',
                        'make',
                        'model',
                        'year',
                        'type',
                        'tags',
                    ],
                ],
                'trailers' => [
                    '*' => [
                        'id',
                        'vin',
                        'unit_number',
                        'license_plate',
                        'make',
                        'model',
                        'year',
                        'tags',
                    ],
                ],
            ]]);

        $this->assertCount(2, $response['data']['trucks']);
        $this->assertCount(1, $response['data']['trailers']);
    }
}
