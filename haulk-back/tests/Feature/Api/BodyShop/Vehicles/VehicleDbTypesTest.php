<?php

namespace Api\BodyShop\Vehicles;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VehicleDbTypesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_show_all_vehicle_types(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.vehicle-db.types'))
            ->assertOk();
    }
}
