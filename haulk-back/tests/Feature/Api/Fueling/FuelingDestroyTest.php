<?php

namespace Tests\Feature\Api\Fueling;

use App\Models\Fueling\FuelCard;
use App\Models\Fueling\Fueling;
use App\Models\Orders\Order;
use App\Models\Users\User;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class FuelingDestroyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_delete_for_unauthorized_users()
    {
        $fueling = Fueling::factory()->create();

        $this->deleteJson(route('fueling.destroy', $fueling))->assertUnauthorized();
    }

    public function test_it_not_delete_for_not_permitted_users()
    {
        $fueling = Fueling::factory()->create();

        $this->loginAsCarrierDispatcher();

        $this->deleteJson(route('fueling.destroy', $fueling))
            ->assertForbidden();
    }

    public function test_it_delete_by_super_admin()
    {
        $fueling = Fueling::factory()->create();

        $this->assertDatabaseHas(Fueling::TABLE_NAME, $fueling->getAttributes());

        $this->loginAsCarrierSuperAdmin();
        $this->deleteJson(route('fueling.destroy', $fueling))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(Fueling::TABLE_NAME, $fueling->getAttributes());
    }

    public function test_it_delete_by_admin()
    {
        $fueling = Fueling::factory()->create();

        $this->assertDatabaseHas(Fueling::TABLE_NAME, $fueling->getAttributes());

        $this->loginAsCarrierAdmin();
        $this->deleteJson(route('fueling.destroy', $fueling))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(Fueling::TABLE_NAME, $fueling->getAttributes());
    }

    public function test_it_delete_with_related_entities()
    {
        $this->markTestSkipped();

    }
}
