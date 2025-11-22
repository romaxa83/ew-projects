<?php

namespace Tests\Feature\Api\Users\Users;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserDispatchersTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_show_list_active_dispatchers()
    {
        $this->loginAsCarrierSuperAdmin();

        $response = $this->getJson(route('users.dispatchers'))
            ->assertOk();

        $initialCount = count($response->json('data'));

        /** @var User $user1 */
        $user1 = User::factory()->create();
        $user1->syncRoles(User::DISPATCHER_ROLE);

        /** @var User $user2 */
        $user2 = User::factory()->create();
        $user2->syncRoles(User::DISPATCHER_ROLE);

        /** @var User $notActiveUser */
        $notActiveUser = User::factory()->create(['status' => User::STATUS_INACTIVE]);
        $notActiveUser->syncRoles(User::DISPATCHER_ROLE);

        $response = $this->getJson(route('users.dispatchers'))
            ->assertOk();

        $this->assertCount($initialCount + 2, json_to_array($response->getContent())['data']);
    }
}
