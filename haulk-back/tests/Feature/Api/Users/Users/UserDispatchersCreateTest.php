<?php

namespace Tests\Feature\Api\Users\Users;

use App\Models\Users\User;
use Tests\UserTestCase;

class UserDispatchersCreateTest extends UserTestCase
{
    public function test_it_create_dispatcher_success()
    {
        $this->loginAsCarrierSuperAdmin();

        $request = [
            'full_name' => 'Some name',
            'email' => 'some.dispatcher@example.com',
            'phone' => '1-541-754-3010',
            'can_check_orders' => true,
        ];

        $requestDb = [
            'first_name' => 'Some',
            'last_name' => 'name',
            'email' => 'some.dispatcher@example.com',
            'phone' => '1-541-754-3010',
            'can_check_orders' => true,
        ];

        $role = [
            'role_id' => $this->roleRepository->findByName(User::DISPATCHER_ROLE)->id,
        ];

        $this->assertDatabaseMissing(User::TABLE_NAME, $requestDb);

        $this->postJson(route('users.store'), $request + $role)
            ->assertCreated()
            ->assertJson(
                [
                    'data' => [
                        'full_name' => 'Some name',
                        'email' => 'some.dispatcher@example.com',
                        'phone' => '1-541-754-3010',
                        'can_check_orders' => true,
                    ]
                ]
            );

        $this->assertDatabaseHas(User::TABLE_NAME, $requestDb);
    }
}
