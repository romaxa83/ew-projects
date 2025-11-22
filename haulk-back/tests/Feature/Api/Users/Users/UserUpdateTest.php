<?php

namespace Tests\Feature\Api\Users\Users;

use App\Broadcasting\Events\User\UpdateUserBroadcast;
use App\Models\Users\User;
use App\Repositories\Roles\RoleRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
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

        $this->getJson(route('users.update', $user))->assertUnauthorized();
    }

    public function test_it_update_user_for_dispatcher_role()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->syncRoles(User::DISPATCHER_ROLE);

        $formRequest = [
            'full_name' => 'new full name',
            'phone' => '1-541-754-3010',
            'email' => 'email@example.com',
        ];

        $dbAttributes = [
            'first_name' => 'new',
            'last_name' => 'full name',
            'phone' => '1-541-754-3010',
            'email' => 'email@example.com',
        ];

        $roleModel = $this->roleRepository->findByName(User::ACCOUNTANT_ROLE);
        $role = [
            'role_id' => $roleModel->id,
        ];

        $this->assertDatabaseMissing(User::TABLE_NAME, $dbAttributes);

        $this->loginAsCarrierSuperAdmin();

        Event::fake([UpdateUserBroadcast::class]);

        $this->postJson(route('users.update', $user->id), $formRequest + $role)
            ->assertOk();

        Event::assertDispatched(UpdateUserBroadcast::class);

        $this->assertDatabaseHas(User::TABLE_NAME, $dbAttributes);

        $user->refresh();
        $this->assertEquals($roleModel->getAttribute('name'), $user->getRoleName());
    }

    public function test_it_cat_update_user_with_attachments()
    {
        $this->loginAsCarrierSuperAdmin();

        /** @var User $user */
        $user = User::factory()->create();
        $user->syncRoles(User::DISPATCHER_ROLE);

        $formRequest = [
            'full_name' => 'new full name',
            'phone' => '1-541-754-3010',
            'email' => 'email@example.com',
            User::ATTACHMENT_FIELD_NAME => [
                UploadedFile::fake()->image('image1.jpg'),
                UploadedFile::fake()->image('image2.jpg'),
                UploadedFile::fake()->createWithContent('info.txt', 'Some text for user file'),
            ],
        ];

        $role = [
            'role_id' => $this->roleRepository->findByName('Dispatcher')->id,
        ];

        Event::fake([UpdateUserBroadcast::class]);

        $response = $this->postJson(route('users.update', $user), $formRequest + $role)
            ->assertOk();

        Event::assertDispatched(UpdateUserBroadcast::class);

        $user = $response->json('data');

        $this->assertCount(3, $user[User::ATTACHMENT_COLLECTION_NAME]);
    }
}
