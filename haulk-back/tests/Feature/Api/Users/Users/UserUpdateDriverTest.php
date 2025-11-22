<?php

namespace Tests\Feature\Api\Users\Users;

use App\Models\Users\DriverInfo;
use App\Models\Users\User;
use App\Repositories\Roles\RoleRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserUpdateDriverTest extends TestCase
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

    public function test_it_update_user_with_role_driver()
    {
        $this->withoutExceptionHandling();
        $this->loginAsCarrierSuperAdmin();

        $user = User::factory()->create();
        $user->syncRoles(User::DRIVER_ROLE);

        $formRequest = [
            'full_name' => 'Some name',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
            'role_id' => $this->roleRepository->findByName(User::DRIVER_ROLE)->id,
            'owner_id' => $this->authenticatedUser->id,
        ];

        $this->loginAsCarrierSuperAdmin();
        $this->postJson(route('users.update', $user), $formRequest)
            ->assertOk();

        $this->assertDatabaseHas(
            DriverInfo::TABLE_NAME,
            [
                'driver_id' => $user->id,
            ]
        );
    }
}
