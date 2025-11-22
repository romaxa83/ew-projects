<?php

namespace Tests\Feature\Api\Users\Users;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RolesListTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_give_client_error_for_not_authorized_users()
    {
        $this->getJson(route('users.role-list'))
            ->assertUnauthorized();
    }

    public function test_it_give_client_error_for_permitted_users()
    {
        self::markTestSkipped();
        $this->loginAsCarrierAccountant();

        $this->getJson(route('users.role-list'))
            ->assertForbidden();
    }

    public function test_it_give_all_roles_for_super_admin_users()
    {
        $this->loginAsCarrierSuperAdmin();

        $this->getJson(route('users.role-list'))
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        [
                            'name' => 'Admin',
                        ],
                        [
                            'name' => 'Dispatcher',
                        ],
                        [
                            'name' => 'Driver',
                        ],
                        [
                            'name' => 'Accountant',
                        ],
                    ]
                ]
            );
    }

    public function test_it_give_all_roles_for_admin_users()
    {
        $this->loginAsCarrierAdmin();

        $this->getJson(route('users.role-list'))
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        [
                            'name' => 'Admin',
                        ],
                        [
                            'name' => 'Dispatcher',
                        ],
                        [
                            'name' => 'Driver',
                        ],
                        [
                            'name' => 'Accountant',
                        ],
                    ]
                ]
            );
    }

}
