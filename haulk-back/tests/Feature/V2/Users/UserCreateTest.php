<?php

namespace Tests\Feature\V2\Users;

use App\Broadcasting\Events\User\CreateUserBroadcast;
use App\Models\Users\User;
use App\Repositories\Roles\RoleRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UserCreateTest extends TestCase
{
    use DatabaseTransactions;

    private RoleRepository $roleRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->roleRepository = resolve(RoleRepository::class);
    }

    public function test_it_forbidden_to_users_create_for_not_authorized_users(): void
    {
        $this->postJson(route('v2.carrier.users.store'), [])->assertUnauthorized();
    }

    public function test_it_create_user_with_dispatcher_role(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $formRequest = [
            'first_name' => 'Some',
            'last_name' => 'name',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
        ];

        $role = [
            'role_id' => $this->roleRepository->findByName('Dispatcher')->id,
        ];

        $this->assertDatabaseMissing(User::TABLE_NAME, $formRequest);

        Event::fake([
            CreateUserBroadcast::class
        ]);

        $this->postJson(route('v2.carrier.users.store'), $formRequest + $role)
            ->assertCreated();

        Event::assertDispatched(CreateUserBroadcast::class);

        $this->assertDatabaseHas(User::TABLE_NAME, $formRequest);
    }

    public function test_it_create_user_validation_error(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $formRequest = [
            'first_name' => 'Some',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
        ];

        $role = [
            'role_id' => $this->roleRepository->findByName('Dispatcher')->id,
        ];

        $this->assertDatabaseMissing(User::TABLE_NAME, $formRequest);

        $this->postJson(route('v2.carrier.users.store'), $formRequest + $role)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseMissing(User::TABLE_NAME, $formRequest);
    }
}
