<?php

namespace Tests\Feature\V2\Users;

use App\Broadcasting\Events\User\UpdateUserBroadcast;
use App\Models\Users\User;
use App\Repositories\Roles\RoleRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UserUpdateTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var RoleRepository
     */
    private $roleRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->roleRepository = resolve(RoleRepository::class);
    }

    public function test_it_not_update_user_for_unauthorized_users()
    {
        $user = User::factory()->create();
        $user->assignRole(User::DISPATCHER_ROLE);

        $this->postJson(route('v2.carrier.users.update', $user), [])->assertUnauthorized();
    }

    public function test_it_update_user_for_dispatcher_role()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->syncRoles(User::DISPATCHER_ROLE);

        $formRequest = [
            'first_name' => 'new',
            'last_name' => 'full name',
            'phone' => '1-541-754-3010',
            'email' => 'email@example.com',
        ];

        $roleModel = $this->roleRepository->findByName(User::ACCOUNTANT_ROLE);
        $role = [
            'role_id' => $roleModel->id,
        ];

        $this->assertDatabaseMissing(User::TABLE_NAME, $formRequest);

        $this->loginAsCarrierSuperAdmin();

        Event::fake([UpdateUserBroadcast::class]);

        $this->postJson(route('v2.carrier.users.update', $user->id), $formRequest + $role)
            ->assertOk();

        Event::assertDispatched(UpdateUserBroadcast::class);

        $this->assertDatabaseHas(User::TABLE_NAME, $formRequest);

        $user->refresh();
        $this->assertEquals($roleModel->getAttribute('name'), $user->getRoleName());
    }
}
