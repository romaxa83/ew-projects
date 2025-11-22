<?php

namespace Tests\Feature\Saas\Admins;

use App\Models\Admins\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminProfileUpdateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_not_auth_admin_cant_update_self_profile(): void
    {
        $this->putJson(route('v1.saas.profile.profile-update'))
            ->assertUnauthorized();
    }

    public function test_a_auth_admin_can_update_self_profile(): void
    {
        $admin = $this->loginAsSaasAdmin();

        $newAttr = [
            'full_name' => 'New Full Name',
            'phone' => '1234567891',
        ];

        $this->assertDatabaseMissing(Admin::TABLE, ['id' => $admin->id] + $newAttr);

        $this->putJson(route('v1.saas.profile.profile-update'), $newAttr)
            ->assertOk();

        $this->assertDatabaseHas(Admin::TABLE, ['id' => $admin->id] + $newAttr);
    }
}
