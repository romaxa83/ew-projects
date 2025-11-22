<?php

namespace Api\BodyShop\Inventories\Units;

use App\Models\BodyShop\Inventories\Unit;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UnitUpdateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_update_for_unauthorized_users()
    {
        $unit = factory(Unit::class)->create();

        $this->putJson(route('body-shop.inventory-units.update', $unit))->assertUnauthorized();
    }

    public function test_it_update_by_bs_super_admin()
    {
        $unit = factory(Unit::class)->create();

        $formRequest = [
            'name' => 'Name Test',
            'accept_decimals' => false,
        ];

        $this->assertDatabaseMissing(Unit::TABLE_NAME, $formRequest);

        $this->loginAsBodyShopSuperAdmin();

        $this->putJson(route('body-shop.inventory-units.update', $unit), $formRequest)
            ->assertOk();

        $this->assertDatabaseHas(Unit::TABLE_NAME, $formRequest);
    }
}
