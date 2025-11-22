<?php

namespace Tests\Feature\Api\Vehicles\Trucks;

use App\Models\Tags\Tag;
use App\Models\Users\User;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class TruckIndexFilterTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    protected string $routeName = 'trucks.index';

    public function test_search(): void
    {
        $this->loginAsCarrierSuperAdmin();

        factory(Truck::class)->create([
            'vin' => 'TEST123kjhjkh',
            'unit_number' => 'DF12',
            'license_plate' => 'FD1-123',
            'temporary_plate' => 'GGDD-234',
        ]);

        factory(Truck::class)->create([
            'vin' => 'HFJ123kjhjkh',
            'unit_number' => 'TE123',
            'license_plate' => 'RFD-234',
            'temporary_plate' => 'DDD-234',
        ]);

        factory(Truck::class)->create([
            'vin' => 'GMGI',
            'unit_number' => 'KG34',
            'license_plate' => 'HFIV-234',
            'temporary_plate' => 'ABVU-234',
        ]);

        $filter = ['q' => 'TEST'];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $trucks = $response->json('data');
        $this->assertCount(1, $trucks);

        $filter = ['q' => '123'];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $trucks = $response->json('data');
        $this->assertCount(2, $trucks);

        $filter = ['q' => '234'];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $trucks = $response->json('data');
        $this->assertCount(3, $trucks);
    }

    public function test_filter_by_driver(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $driver1 = $this->driverFactory();
        factory(Truck::class)->times(3)->create(['driver_id' => $driver1->id]);

        $driver2 = $this->driverFactory();
        factory(Truck::class)->times(2)->create(['driver_id' => $driver2->id]);

        $filter = ['driver_id' => $driver2->id];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $trucks = $response->json('data');
        $this->assertCount(2, $trucks);
    }

    public function test_filter_by_owner(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $owner1 = User::factory()->create();
        factory(Truck::class)->times(3)->create(['owner_id' => $owner1->id]);

        $owner2 = User::factory()->create();
        factory(Truck::class)->times(2)->create(['owner_id' => $owner2->id]);

        $filter = ['owner_id' => $owner1->id];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $trucks = $response->json('data');
        $this->assertCount(3, $trucks);
    }

    public function test_filter_by_tag(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $tag1 = Tag::factory()->create(['type' => Tag::TYPE_TRUCKS_AND_TRAILER]);
        $tag2 = Tag::factory()->create(['type' => Tag::TYPE_TRUCKS_AND_TRAILER]);

        $truck1 = factory(Truck::class)->create();
        $truck1->tags()->sync([$tag1->id]);

        $truck2 = factory(Truck::class)->create();
        $truck2->tags()->sync([$tag2->id]);

        $truck3 = factory(Truck::class)->create();
        $truck3->tags()->sync([$tag1->id, $tag2->id]);

        $truck4 = factory(Truck::class)->create();
        $truck4->tags()->sync([$tag1->id]);

        $filter = ['tag_id' => $tag1->id];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $trucks = $response->json('data');
        $this->assertCount(3, $trucks);
    }

    public function test_sort_by_inspection_expiration_date(): void
    {
        $this->loginAsCarrierAdmin();

        $truck1 = factory(Truck::class)->create(['inspection_expiration_date' => now()]);
        $truck2 = factory(Truck::class)->create(['inspection_expiration_date' => now()->addDays(3)]);
        $truck3 = factory(Truck::class)->create(['inspection_expiration_date' => now()->addDays(-5)]);

        $filter = ['order_by' => 'inspection_expiration_date', 'order_type' => 'asc'];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $this->assertEquals(
            [
                $truck3->id,
                $truck1->id,
                $truck2->id,
            ],
            array_column($response['data'], 'id')
        );

        $filter = ['order_by' => 'inspection_expiration_date', 'order_type' => 'desc'];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $this->assertEquals(
            [
                $truck2->id,
                $truck1->id,
                $truck3->id,
            ],
            array_column($response['data'], 'id')
        );
    }

    public function test_sort_by_registration_expiration_date(): void
    {
        $this->loginAsCarrierAdmin();

        $truck1 = factory(Truck::class)->create(['registration_expiration_date' => now()]);
        $truck2 = factory(Truck::class)->create(['registration_expiration_date' => now()->addDays(3)]);
        $truck3 = factory(Truck::class)->create(['registration_expiration_date' => now()->addDays(-5)]);

        $filter = ['order_by' => 'registration_expiration_date', 'order_type' => 'asc'];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $this->assertEquals(
            [
                $truck3->id,
                $truck1->id,
                $truck2->id,
            ],
            array_column($response['data'], 'id')
        );

        $filter = ['order_by' => 'registration_expiration_date', 'order_type' => 'desc'];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $this->assertEquals(
            [
                $truck2->id,
                $truck1->id,
                $truck3->id,
            ],
            array_column($response['data'], 'id')
        );
    }
}
