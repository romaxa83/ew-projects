<?php

namespace Api\BodyShop\Vehicles;

use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VehicleDbVehiclesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_show_all_vehicle_types(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        factory(Truck::class)->create(['vin' => 'TEST43534534', 'carrier_id' => null]);
        factory(Truck::class)->create(['carrier_id' => null]);
        factory(Trailer::class)->create(['unit_number' => 'TEST', 'carrier_id' => null]);
        factory(Trailer::class)->create(['carrier_id' => null]);

        $response = $this->getJson(route('body-shop.vehicle-db.vehicles', ['q' => 'TEST']))
            ->assertOk();

        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_it_search_by_id(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        factory(Truck::class)->create(['carrier_id' => null]);
        factory(Truck::class)->create(['carrier_id' => null]);
        $trailer = factory(Trailer::class)->create(['carrier_id' => null]);
        factory(Trailer::class)->create(['carrier_id' => null]);

        $response = $this->getJson(route('body-shop.vehicle-db.vehicles', ['searchid' => $trailer->id]))
            ->assertOk();

        $data = $response->json('data');
        foreach ($data as $item) {
            $this->assertEquals($trailer->id, $item['id']);
        }
    }
}
