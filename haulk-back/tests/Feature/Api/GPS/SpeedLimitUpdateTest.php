<?php

namespace Api\GPS;

use App\Models\Saas\Company\Company;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SpeedLimitUpdateTest extends TestCase
{
    use DatabaseTransactions;
    public function test_it_not_update_for_unauthorized_users()
    {
           $this->putJson(route('gps.update-speed-limit'), ['speed_limit' => 82.5])
               ->assertUnauthorized();
    }

    public function test_it_forbidden_for_dispatcher()
    {
        $user = $this->loginAsCarrierDispatcher();

        $this->putJson(route('gps.update-speed-limit'), ['speed_limit' => 82.5])
            ->assertForbidden();

        $this->assertDatabaseMissing(Company::TABLE_NAME, [
            'id' => $user->getCompanyId(),
            'speed_limit' => 82.5
        ]);
    }

    public function test_it_update_by_super_admin()
    {
        $user = $this->loginAsCarrierSuperAdmin();

        $this->putJson(route('gps.update-speed-limit'), ['speed_limit' => 82.5])
            ->assertOk();

        $this->assertDatabaseHas(Company::TABLE_NAME, [
            'id' => $user->getCompanyId(),
            'speed_limit' => 82.5
        ]);
    }
}
