<?php

namespace Api\BodyShop\VehicleOwners;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class VehicleOwnerShortlistTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_search()
    {
        $this->loginAsBodyShopAdmin();

        factory(VehicleOwner::class)->create([
            'first_name' => 'Name1',
            'last_name' => 'Last',
            'phone' => '1234569887',
            'email' => 'test@test.com',
        ]);

        factory(VehicleOwner::class)->create([
            'first_name' => 'Name2',
            'last_name' => '2Last',
            'phone' => '1232169887',
            'email' => 'test2@test.com',
        ]);

        factory(VehicleOwner::class)->create([
            'first_name' => 'Name3',
            'last_name' => '3Last',
            'phone' => '1234449887',
            'email' => 'Name1@test.com',
        ]);

        $filter = ['q' => 'Name1'];
        $response = $this->getJson(route('body-shop.vehicle-owners.shortlist', $filter))
            ->assertOk();

        $vehicleOwners = $response->json('data');
        $this->assertCount(2, $vehicleOwners);
    }

    public function test_search_by_id()
    {
        $this->loginAsBodyShopAdmin();

        $owner = factory(VehicleOwner::class)->create();
        factory(VehicleOwner::class)->create();

        $filter = ['searchid' => $owner->id];
        $response = $this->getJson(route('body-shop.vehicle-owners.shortlist', $filter))
            ->assertOk();

        $vehicleOwners = $response->json('data');
        $this->assertCount(1, $vehicleOwners);
    }
}
