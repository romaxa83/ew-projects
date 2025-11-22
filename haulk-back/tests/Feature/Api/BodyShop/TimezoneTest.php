<?php

namespace Api\BodyShop;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TimezoneTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_show_cities_for_permitted_users()
    {
        $this->getJson(route('body-shop.timezone-list'))
            ->assertOk();
    }
}
