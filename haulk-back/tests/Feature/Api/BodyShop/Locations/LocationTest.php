<?php

namespace Api\BodyShop\Locations;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LocationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_show_cities_for_permitted_users()
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.city-autocomplete'))
            ->assertOk();
    }

    public function test_it_show_states_for_permitted_users()
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.states.list'))
            ->assertOk();
    }
}
