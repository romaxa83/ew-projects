<?php

namespace Api\BodyShop\Settings;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SettingsShowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_show_for_permitted_users()
    {

        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.settings.show-info'))
            ->assertOk();
    }
}
