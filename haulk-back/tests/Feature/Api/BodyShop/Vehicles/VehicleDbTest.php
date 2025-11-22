<?php

namespace Api\BodyShop\Vehicles;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VehicleDbTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_show_vehicle_makes(): void
    {
        $this->loginAsBodyShopAdmin();

        $this->getJson(route('body-shop.vehicle-db.makes', ['s' => 'FORD']))
            ->assertOk();
    }

    public function test_it_show_vehicle_models(): void
    {
        $this->loginAsBodyShopAdmin();

        $this->getJson(route('body-shop.vehicle-db.models', ['s' => 's2']))
            ->assertOk();
    }
}
