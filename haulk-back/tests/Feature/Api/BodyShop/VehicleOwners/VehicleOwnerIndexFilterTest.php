<?php

namespace Tests\Feature\Api\BodyShop\VehicleOwners;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Tags\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class VehicleOwnerIndexFilterTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_search()
    {
        $this->loginAsBodyShopAdmin();

        $owner1 = factory(VehicleOwner::class)->create([
            'first_name' => 'Name1',
            'last_name' => 'Last',
            'phone' => '1234569887',
            'email' => 'test@test.com',
        ]);

        $owner2 = factory(VehicleOwner::class)->create([
            'first_name' => 'Name2',
            'last_name' => '2Last',
            'phone' => '1232169887',
            'email' => 'test2@test.com',
        ]);

        $owner3 = factory(VehicleOwner::class)->create([
            'first_name' => 'Name3',
            'last_name' => '3Last',
            'phone' => '1234449887',
            'email' => 'test3@test.com',
        ]);

        $filter = ['q' => 'Name1'];
        $response = $this->getJson(route('body-shop.vehicle-owners.index', $filter))
            ->assertOk();

        $vehicleOwners = $response->json('data');
        $this->assertCount(1, $vehicleOwners);
        $this->assertEquals($owner1->id, $vehicleOwners[0]['id']);

        $filter = ['q' => 'Name2 2'];
        $response = $this->getJson(route('body-shop.vehicle-owners.index', $filter))
            ->assertOk();

        $vehicleOwners = $response->json('data');
        $this->assertCount(1, $vehicleOwners);
        $this->assertEquals($owner2->id, $vehicleOwners[0]['id']);

        $filter = ['q' => '1234449887'];
        $response = $this->getJson(route('body-shop.vehicle-owners.index', $filter))
            ->assertOk();

        $vehicleOwners = $response->json('data');
        $this->assertCount(1, $vehicleOwners);
        $this->assertEquals($owner3->id, $vehicleOwners[0]['id']);

        $filter = ['q' => 'test2'];
        $response = $this->getJson(route('body-shop.vehicle-owners.index', $filter))
            ->assertOk();

        $vehicleOwners = $response->json('data');
        $this->assertCount(1, $vehicleOwners);
        $this->assertEquals($owner2->id, $vehicleOwners[0]['id']);
    }

    public function test_filter_by_tag(): void
    {
        $this->loginAsBodyShopAdmin();

        $tag1 = Tag::factory()->create(['type' => Tag::TYPE_VEHICLE_OWNER, 'carrier_id' => null]);
        $tag2 = Tag::factory()->create(['type' => Tag::TYPE_VEHICLE_OWNER, 'carrier_id' => null]);

        $owner1 = factory(VehicleOwner::class)->create();
        $owner1->tags()->sync([$tag1->id]);
        $owner2 = factory(VehicleOwner::class)->create();
        $owner2->tags()->sync([$tag1->id]);
        $owner3 = factory(VehicleOwner::class)->create();
        $owner3->tags()->sync([$tag2->id]);

        $response = $this->getJson(route('body-shop.vehicle-owners.index', ['tag_id' => $tag1->id]))
            ->assertOk();
        $vehicleOwners = $response->json('data');
        $this->assertCount(2, $vehicleOwners);
    }
}
