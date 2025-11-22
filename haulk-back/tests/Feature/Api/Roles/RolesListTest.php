<?php

namespace Tests\Feature\Api\Roles;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RolesListTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_give_client_error_for_not_authorized_users()
    {
        $this->getJson(route('roles.list'))
            ->assertUnauthorized();
    }

    public function test_it_give_all_roles_for_super_admin_users()
    {
        $this->loginAsCarrierSuperAdmin();

        $response = $this->getJson(route('roles.list'))
            ->assertOk();

        $roles = $response->json('data');
        $this->assertNotEmpty($roles);

        foreach ($roles as $role) {
            $this->assertFalse(in_array($role['name'], User::BS_ROLES));
        }
    }
}
