<?php

namespace Api\BodyShop\VehicleOwners;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class VehicleOwnerDestroyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_delete_vehicle_owner_for_unauthorized_users()
    {
        $vehicleOwner = factory(VehicleOwner::class)->create();

        $this->deleteJson(route('body-shop.vehicle-owners.destroy', $vehicleOwner))->assertUnauthorized();
    }

    public function test_it_not_delete_vehicle_owner_for_not_permitted_users()
    {
        $vehicleOwner = factory(VehicleOwner::class)->create();

        $this->loginAsCarrierSuperAdmin();

        $this->deleteJson(route('body-shop.vehicle-owners.destroy', $vehicleOwner))
            ->assertForbidden();
    }

    public function test_it_delete_vehicle_owner_of_body_shop()
    {
        $vehicleOwner = factory(VehicleOwner::class)->create();

        $this->assertDatabaseHas(VehicleOwner::TABLE_NAME, $vehicleOwner->getAttributes());

        $this->loginAsBodyShopSuperAdmin();
        $this->deleteJson(route('body-shop.vehicle-owners.destroy', $vehicleOwner))
            ->assertNoContent();

        $this->assertDatabaseMissing(VehicleOwner::TABLE_NAME, $vehicleOwner->getAttributes());
    }

    public function test_it_delete_vehicle_owner_of_body_shop_by_bs_admin()
    {
        $vehicleOwner = factory(VehicleOwner::class)->create();

        $this->assertDatabaseHas(VehicleOwner::TABLE_NAME, $vehicleOwner->getAttributes());

        $this->loginAsBodyShopAdmin();
        $this->deleteJson(route('body-shop.vehicle-owners.destroy', $vehicleOwner))
            ->assertNoContent();

        $this->assertDatabaseMissing(VehicleOwner::TABLE_NAME, $vehicleOwner->getAttributes());
    }

    public function test_it_delete_vehicle_owner_with_related_vehicles()
    {
        $vehicleOwner = factory(VehicleOwner::class)->create();
        factory(Truck::class)->create(['carrier_id' => null,'customer_id' => $vehicleOwner->id]);

        $this->assertDatabaseHas(VehicleOwner::TABLE_NAME, $vehicleOwner->getAttributes());

        $this->loginAsBodyShopAdmin();
        $this->deleteJson(route('body-shop.vehicle-owners.destroy', $vehicleOwner))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(VehicleOwner::TABLE_NAME, $vehicleOwner->getAttributes());
    }
}
