<?php

namespace Tests\Feature\Api\BodyShop\Vehicles\Trucks;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Saas\Company\Company;
use App\Models\Tags\Tag;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class TruckIndexFilterTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    protected string $routeName = 'body-shop.trucks.index';

    public function test_search(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $company = Company::factory()->create(['use_in_body_shop' => true]);

        factory(Truck::class)->create([
            'vin' => 'TEST123kjhjkh',
            'unit_number' => 'DF12',
            'license_plate' => 'FD1-123',
            'carrier_id' => $company->id,
        ]);

        factory(Truck::class)->create([
            'vin' => 'HFJ123kjhjkh',
            'unit_number' => 'TE123',
            'license_plate' => 'RFD-234',
            'carrier_id' => $company->id,
        ]);

        factory(Truck::class)->create([
            'vin' => 'GMGI',
            'unit_number' => 'KG34',
            'license_plate' => 'HFIV-234',
            'carrier_id' => null,
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
        $this->assertCount(2, $trucks);
    }

    public function test_filter_by_driver(): void
    {
        $this->loginAsBodyShopAdmin();

        $company = Company::factory()->create(['use_in_body_shop' => true]);

        $driver1 = $this->driverFactory(['carrier_id' => $company->id]);
        factory(Truck::class)->times(3)->create([
            'driver_id' => $driver1->id,
            'carrier_id' => $company->id,
        ]);

        $driver2 = $this->driverFactory(['carrier_id' => $company->id]);
        factory(Truck::class)->times(2)->create([
            'driver_id' => $driver2->id,
            'carrier_id' => $company->id,
        ]);

        $filter = ['driver_id' => $driver2->id];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $trucks = $response->json('data');
        $this->assertCount(2, $trucks);
    }

    public function test_filter_by_tag(): void
    {
        $this->loginAsBodyShopAdmin();

        $tag1 = Tag::factory()->create(['type' => Tag::TYPE_TRUCKS_AND_TRAILER, 'carrier_id' => null]);
        $tag2 = Tag::factory()->create(['type' => Tag::TYPE_TRUCKS_AND_TRAILER]);

        $truck1 = factory(Truck::class)->create(['carrier_id' => null]);
        $truck1->tags()->sync([$tag1->id]);

        $truck2 = factory(Truck::class)->create(['carrier_id' => null]);
        $truck2->tags()->sync([$tag2->id]);

        $truck3 = factory(Truck::class)->create(['carrier_id' => null]);
        $truck3->tags()->sync([$tag1->id, $tag2->id]);

        $truck4 = factory(Truck::class)->create(['carrier_id' => null]);
        $truck4->tags()->sync([$tag1->id]);

        $filter = ['tag_id' => $tag1->id];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $trucks = $response->json('data');
        $this->assertCount(3, $trucks);
    }

    public function test_filter_by_customer(): void
    {
        $this->loginAsBodyShopAdmin();

        $customer1  = factory(VehicleOwner::class)->create();
        $customer2  = factory(VehicleOwner::class)->create();

        factory(Truck::class)->create(['carrier_id' => null, 'customer_id' => $customer1->id]);
        factory(Truck::class)->create(['carrier_id' => null, 'customer_id' => $customer1->id]);
        factory(Truck::class)->create(['carrier_id' => null, 'customer_id' => $customer1->id]);
        factory(Truck::class)->create(['carrier_id' => null, 'customer_id' => $customer2->id]);

        $filter = ['customer_id' => $customer1->id];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $trucks = $response->json('data');
        $this->assertCount(3, $trucks);
    }
}
