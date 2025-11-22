<?php

namespace Tests\Feature\Api\Users\Users;

use App\Broadcasting\Events\User\CreateUserBroadcast;
use App\Models\Users\User;
use App\Repositories\Roles\RoleRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
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

    public function test_it_forbidden_to_users_create_for_not_authorized_users()
    {
        $this->postJson(route('users.store'), [])->assertUnauthorized();
    }

    public function test_it_create_user_with_dispatcher_role()
    {
        $this->loginAsCarrierSuperAdmin();

        $formRequest = [
            'full_name' => 'Some name',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
        ];

        $formRequestDb = [
            'first_name' => 'Some',
            'last_name' => 'name',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
        ];

        $role = [
            'role_id' => $this->roleRepository->findByName('Dispatcher')->id,
        ];

        $this->assertDatabaseMissing(User::TABLE_NAME, $formRequestDb);

        Event::fake([
            CreateUserBroadcast::class
        ]);

        $this->postJson(route('users.store'), $formRequest + $role)
            ->assertCreated();

        Event::assertDispatched(CreateUserBroadcast::class);

        $this->assertDatabaseHas(User::TABLE_NAME, $formRequestDb);
    }

    public function test_it_cat_create_new_user_with_attachments()
    {
        $this->loginAsCarrierSuperAdmin();

        $formRequest = [
            'full_name' => 'Some name',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
            User::ATTACHMENT_FIELD_NAME => [
                UploadedFile::fake()->image('image1.jpg'),
                UploadedFile::fake()->image('image2.jpg'),
                UploadedFile::fake()->createWithContent('info.txt', 'Some text for user file'),
            ],
        ];

        $role = [
            'role_id' => $this->roleRepository->findByName('Dispatcher')->id,
        ];

        Event::fake([
            CreateUserBroadcast::class
        ]);

        $response = $this->postJson(route('users.store'), $formRequest + $role)
            ->assertCreated();

        Event::assertDispatched(CreateUserBroadcast::class);

        $user = $response->json('data');

        $this->assertCount(3, $user[User::ATTACHMENT_COLLECTION_NAME]);
    }

    public function test_it_create_new_user_with_not_completely_filled_phone()
    {
        $this->loginAsCarrierSuperAdmin();

        $formRequest = [
            'full_name' => 'Some name',
            'email' => 'some.email@example.com',
        ];

        $formRequestDb = [
            'first_name' => 'Some',
            'last_name' => 'name',
            'email' => 'some.email@example.com',
        ];

        $phone = ['phone' => '+123'];

        $role = [
            'role_id' => $this->roleRepository->findByName('Dispatcher')->id,
        ];

        $this->assertDatabaseMissing(User::TABLE_NAME, $formRequestDb);

        Event::fake([
            CreateUserBroadcast::class
        ]);

        $this->postJson(route('users.store'), $formRequest + $role + $phone)
            ->assertCreated();

        Event::assertDispatched(CreateUserBroadcast::class);

        $this->assertDatabaseHas(User::TABLE_NAME, $formRequestDb);

        $this->assertDatabaseMissing(User::TABLE_NAME, $phone);
    }
}
