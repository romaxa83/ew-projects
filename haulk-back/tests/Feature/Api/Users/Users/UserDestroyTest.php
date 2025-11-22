<?php

namespace Tests\Feature\Api\Users\Users;

use App\Broadcasting\Events\User\DeleteUserBroadcast;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UserDestroyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_delete_user_for_unauthorized_users()
    {
        $user = User::factory()->create();
        $user->assignRole(User::DISPATCHER_ROLE);

        $this->deleteJson(route('users.destroy', $user))->assertUnauthorized();
    }

    public function test_it_not_delete_user_for_not_permitted_users()
    {
        $user = User::factory()->create();
        $user->assignRole(User::DISPATCHER_ROLE);

        $this->loginAsCarrierDispatcher();

        $this->deleteJson(route('users.destroy', $user))
            ->assertForbidden();
    }


    public function test_it_delete_active_user_for_dispatcher_role()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->syncRoles(User::DISPATCHER_ROLE);

        $this->assertDatabaseHas(User::TABLE_NAME, $user->getAttributes());

        $this->loginAsCarrierSuperAdmin();
        $this->deleteJson(route('users.destroy', $user))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(User::TABLE_NAME, $user->getAttributes());
    }

    public function test_it_delete_not_active_user_for_dispatcher_role()
    {
        /** @var User $user */
        $user = User::factory()->create(['status' => User::STATUS_INACTIVE]);
        $user->syncRoles(User::DISPATCHER_ROLE);

        $this->assertDatabaseHas(User::TABLE_NAME, $user->getAttributes());

        $this->loginAsCarrierSuperAdmin();

        Event::fake([
            DeleteUserBroadcast::class
        ]);

        $this->deleteJson(route('users.destroy', $user))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        Event::assertDispatched(DeleteUserBroadcast::class);

        $this->assertDatabaseMissing(User::TABLE_NAME, $user->getAttributes());
    }
}
