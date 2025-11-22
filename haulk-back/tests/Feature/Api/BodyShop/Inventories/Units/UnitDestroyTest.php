<?php

namespace Api\BodyShop\Inventories\Units;

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Inventories\Unit;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class UnitDestroyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_delete_for_unauthorized_users()
    {
        $unit = factory(Unit::class)->create();

        $this->deleteJson(route('body-shop.inventory-units.destroy', $unit))->assertUnauthorized();
    }

    public function test_it_not_delete_for_not_permitted_users()
    {
        $unit = factory(Unit::class)->create();

        $this->loginAsBodyShopMechanic();

        $this->deleteJson(route('body-shop.inventory-units.destroy', $unit))
            ->assertForbidden();
    }

    public function test_it_delete_by_bs_super_admin()
    {
        $unit = factory(Unit::class)->create();

        $this->assertDatabaseHas(Unit::TABLE_NAME, $unit->getAttributes());

        $this->loginAsBodyShopSuperAdmin();
        $this->deleteJson(route('body-shop.inventory-units.destroy', $unit))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(Unit::TABLE_NAME, $unit->getAttributes());
    }

    public function test_it_delete_by_bs_admin()
    {
        $unit = factory(Unit::class)->create();

        $this->assertDatabaseHas(Unit::TABLE_NAME, $unit->getAttributes());

        $this->loginAsBodyShopAdmin();
        $this->deleteJson(route('body-shop.inventory-units.destroy', $unit))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(Unit::TABLE_NAME, $unit->getAttributes());
    }

    public function test_cant_delete_with_relation()
    {
        $unit = factory(Unit::class)->create();
        factory(Inventory::class)->create(['unit_id' => $unit->id]);

        $this->assertDatabaseHas(Unit::TABLE_NAME, $unit->getAttributes());

        $this->loginAsBodyShopAdmin();
        $this->deleteJson(route('body-shop.inventory-units.destroy', $unit))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(Unit::TABLE_NAME, $unit->getAttributes());
    }
}
