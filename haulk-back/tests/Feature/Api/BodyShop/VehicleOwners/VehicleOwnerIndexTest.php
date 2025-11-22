<?php

namespace Api\BodyShop\VehicleOwners;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VehicleOwnerIndexTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_for_not_authorized_users(): void
    {
        $this->getJson(route('body-shop.vehicle-owners.index'))
            ->assertUnauthorized();
    }

    public function test_it_forbidden_for_permitted_users(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $this->getJson(route('body-shop.vehicle-owners.index'))
            ->assertForbidden();
    }

    public function test_it_show_all_vehicle_owners_for_bs_super_admin(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.vehicle-owners.index'))
            ->assertOk();
    }

    public function test_it_show_all_vehicle_owners_for_body_shop_admin(): void
    {
        $this->loginAsBodyShopAdmin();

        $this->getJson(route('body-shop.vehicle-owners.index'))
            ->assertOk();
    }

    public function test_related_vehicles_fields(): void
    {
        $this->loginAsBodyShopAdmin();

        $owner = factory(VehicleOwner::class)->create();

        $response = $this->getJson(route('body-shop.vehicle-owners.index'))
            ->assertOk();

        $this->assertFalse($response['data'][0]['hasRelatedTrucks']);
        $this->assertFalse($response['data'][0]['hasRelatedTrailers']);

        factory(Trailer::class)->create(['carrier_id' => null, 'customer_id' => $owner->id]);

        $response = $this->getJson(route('body-shop.vehicle-owners.index'))
            ->assertOk();

        $this->assertFalse($response['data'][0]['hasRelatedTrucks']);
        $this->assertTrue($response['data'][0]['hasRelatedTrailers']);

        factory(Truck::class)->create(['carrier_id' => null, 'customer_id' => $owner->id]);

        $response = $this->getJson(route('body-shop.vehicle-owners.index'))
            ->assertOk();

        $this->assertTrue($response['data'][0]['hasRelatedTrucks']);
        $this->assertTrue($response['data'][0]['hasRelatedTrailers']);
    }
}
