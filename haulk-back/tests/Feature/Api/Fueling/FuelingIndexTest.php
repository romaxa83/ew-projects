<?php

namespace Tests\Feature\Api\Fueling;

use App\Models\Fueling\FuelCard;
use App\Models\Fueling\Fueling;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class FuelingIndexTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_forbidden_for_not_authorized_users(): void
    {
        $this->getJson(route('fueling.index'))
            ->assertUnauthorized();
    }

    public function test_it_forbidden_for_permitted_users(): void
    {
        $this->loginAsCarrierDriver();

        $this->getJson(route('fueling.index'))
            ->assertForbidden();
    }

    public function test_it_show_all_for_super_admin(): void
    {
        $this->loginAsCarrierSuperAdmin();
        Fueling::factory()->count(15)->create();
        $response = $this->getJson(route('fueling.index'))
            ->assertOk();

        $comments = $response['data'];
        $this->assertCount(15, $comments);
    }

    public function test_it_show_all_for_admin(): void
    {
        $this->loginAsCarrierAdmin();

        $this->getJson(route('fueling.index'))
            ->assertOk();
    }

    public function test_it_show_all_for_accountant(): void
    {
        $this->loginAsCarrierAccountant();

        $this->getJson(route('fueling.index'))
            ->assertOk();
    }
}
