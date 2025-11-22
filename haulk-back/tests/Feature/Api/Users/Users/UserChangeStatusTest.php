<?php

namespace Tests\Feature\Api\Users\Users;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class UserChangeStatusTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_for_not_authorized_users(): void
    {
        $user = User::factory()->create();
        $user->assignRole(User::DISPATCHER_ROLE);

        $this->putJson(route('users.change-status', $user))
            ->assertUnauthorized();
    }

    public function test_it_forbidden_for_permitted_users(): void
    {
        $this->loginAsCarrierDriver();

        $user = User::factory()->create();
        $user->assignRole(User::DISPATCHER_ROLE);

        $this->putJson(route('users.change-status', $user))
            ->assertForbidden();
    }

    public function test_it_success_to_active(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $user = User::factory()->create(['status' => User::STATUS_INACTIVE]);
        $user->assignRole(User::DISPATCHER_ROLE);

        $this->putJson(route('users.change-status', $user))
            ->assertOk();

        $user->refresh();
        $this->assertEquals(User::STATUS_ACTIVE, $user->status);
    }

    public function test_it_success_to_inactive(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $user = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $user->assignRole(User::DISPATCHER_ROLE);

        $this->putJson(route('users.change-status', $user))
            ->assertOk();

        $user->refresh();
        $this->assertEquals(User::STATUS_INACTIVE, $user->status);
    }

    public function test_it_error_from_pending(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $user = User::factory()->create(['status' => User::STATUS_PENDING]);
        $user->assignRole(User::DISPATCHER_ROLE);

        $this->putJson(route('users.change-status', $user))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'errors' => [
                    [
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        'title' => trans('Can\'t change status for Pending'),
                    ]
                ],
            ]);

        $user->refresh();
        $this->assertEquals(User::STATUS_PENDING, $user->status);
    }
}
