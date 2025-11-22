<?php

namespace Tests\Feature\Api\Users\Users;

use App\Models\Users\DriverInfo;
use App\Models\Users\User;
use App\Repositories\Roles\RoleRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class UserCreateDriverTest extends TestCase
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

    public function test_it_create_new_driver_with_additional_information()
    {
        $this->loginAsCarrierSuperAdmin();

        $formRequest = [
            'full_name' => 'Some name',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
            'owner_id' => $this->authenticatedUser->id,
        ];

        $role = [
            'role_id' => $this->roleRepository->findByName(User::DRIVER_ROLE)->id,
        ];

        $this->postJson(route('users.store'), $formRequest + $role)
            ->assertCreated();

        $this->assertDatabaseHas(
            User::TABLE_NAME,
            [
                'first_name' => 'Some',
                'last_name' => 'name',
                'email' => 'some.email@example.com',
                'phone' => '1-541-754-3010',
                'owner_id' => $this->authenticatedUser->id,
            ]
        );
    }
}
