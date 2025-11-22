<?php

namespace Tests\Feature\Api\Users\Users;

use App\Models\Users\User;
use Tests\UserTestCase;

class UserDispatchersUpdateTest extends UserTestCase
{
    public function test_it_update_dispatcher_success()
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

        /** @var User $user */
        $user = User::factory()->create($requestDb);
        $user->syncRoles($role['role_id']);

        $request['can_check_orders'] = false;
        $requestDb['can_check_orders'] = false;

        $this->assertDatabaseMissing(User::TABLE_NAME, $requestDb);

        $this->postJson(route('users.update', $user), $request + $role)
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        'full_name' => 'Some name',
                        'email' => 'some.dispatcher@example.com',
                        'phone' => '1-541-754-3010',
                        'can_check_orders' => false,
                    ]
                ]
            );

        $this->assertDatabaseHas(User::TABLE_NAME, $requestDb);
    }
}
