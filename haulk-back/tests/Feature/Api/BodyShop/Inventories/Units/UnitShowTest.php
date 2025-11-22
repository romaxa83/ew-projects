<?php

namespace Api\BodyShop\Inventories\Units;

use App\Models\BodyShop\Inventories\Unit;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UnitShowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_show_for_unauthorized_users()
    {
        $unit = factory(Unit::class)->create();

        $this->getJson(route('body-shop.inventory-units.show', $unit))->assertUnauthorized();
    }

    public function test_it_not_show_for_not_permitted_users()
    {
        $unit = factory(Unit::class)->create();

        $this->loginAsCarrierAdmin();

        $this->getJson(route('body-shop.inventory-units.show', $unit))
            ->assertForbidden();
    }

    public function test_it_show_for_permitted_users()
    {
        $unit = factory(Unit::class)->create();

        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.inventory-units.show', $unit))
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'name',
                'accept_decimals',
                'hasRelatedEntities',
            ]]);

        $this->loginAsBodyShopAdmin();

        $this->getJson(route('body-shop.inventory-units.show', $unit))
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'name',
                'accept_decimals',
                'hasRelatedEntities',
            ]]);
    }
}
