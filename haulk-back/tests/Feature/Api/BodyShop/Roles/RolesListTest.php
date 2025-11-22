<?php

namespace Tests\Feature\Api\BodyShop\Roles;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RolesListTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_give_client_error_for_not_authorized_users()
    {
        $this->getJson(route('body-shop.roles.list'))
            ->assertUnauthorized();
    }

    public function test_it_give_all_roles_for_super_admin_users()
    {
        $this->loginAsBodyShopAdmin();

        $response = $this->getJson(route('body-shop.roles.list'))
            ->assertOk();

        $roles = $response->json('data');
        $this->assertNotEmpty($roles);
    }
}
