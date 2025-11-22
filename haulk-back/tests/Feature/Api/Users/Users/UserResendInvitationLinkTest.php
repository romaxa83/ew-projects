<?php

namespace Tests\Feature\Api\Users\Users;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class UserResendInvitationLinkTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_to_users_create_for_not_authorized_users()
    {
        $user = User::factory()->create(['status' => User::STATUS_PENDING]);
        $user->assignRole(User::DISPATCHER_ROLE);

        $this->putJson(route('users.resendInvitationLink', $user), [])->assertUnauthorized();
    }

    public function test_it_success()
    {
        $this->loginAsCarrierSuperAdmin();

        $user = User::factory()->create(['status' => User::STATUS_PENDING]);
        $user->assignRole(User::DISPATCHER_ROLE);

        $this->putJson(route('users.resendInvitationLink', $user))
            ->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function test_it_error_for_not_pending_statuses()
    {
        $this->loginAsCarrierSuperAdmin();

        $user = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $user->assignRole(User::DRIVER_ROLE);

        $this->putJson(route('users.resendInvitationLink', $user))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $user = User::factory()->create(['status' => User::STATUS_INACTIVE]);
        $user->assignRole(User::ACCOUNTANT_ROLE);

        $this->putJson(route('users.resendInvitationLink', $user))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
