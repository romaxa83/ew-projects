<?php

namespace Api\BodyShop\Vehicles\Trucks;

use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TruckSameVinTest extends TestCase
{
    use DatabaseTransactions;
    public function test_it_check_vin(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $vin = 'DHFHF734FGF';
        factory(Truck::class)->create(['vin' => $vin, 'carrier_id' => null]);

        $response = $this->getJson(route('body-shop.trucks.same-vin', ['vin' => $vin]))
            ->assertOk();

        $this->assertCount(1, $response['data']);
    }

    public function test_it_check_vin_without_current_vehicle(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $vin = 'DHFHF734FGF';
        factory(Truck::class)->create(['vin' => $vin, 'carrier_id' => null]);
        $truck = factory(Truck::class)->create(['vin' => $vin, 'carrier_id' => null]);

        $response = $this->getJson(route('body-shop.trucks.same-vin', ['vin' => $vin, 'id' => $truck->id]))
            ->assertOk();

        $this->assertCount(1, $response['data']);
    }
}
