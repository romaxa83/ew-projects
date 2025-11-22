<?php

namespace Tests\Feature\Api\BodyShop\Users;

use App\Broadcasting\Events\User\CreateUserBroadcast;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UserCreateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_to_users_create_for_not_authorized_users(): void
    {
        $this->postJson(route('body-shop.users.store'), [])
            ->assertUnauthorized();
    }

    public function test_it_forbidden_to_users_create_for_not_permitted_users(): void
    {
        $this->loginAsBodyShopMechanic();

        $formRequest = [
            'first_name' => 'FName',
            'last_name' => 'LName',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
        ];

        $roleId = $this->getRoleRepository()->findByName(User::BSADMIN_ROLE)->id;

        $this->postJson(route('body-shop.users.store'), $formRequest + ['role_id' => $roleId])
            ->assertForbidden();
    }

    public function test_bs_s_admin_can_create_admin(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $formRequest = [
            'first_name' => 'FName',
            'last_name' => 'LName',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
        ];

        $roleId = $this->getRoleRepository()->findByName(User::BSADMIN_ROLE)->id;

        $this->assertDatabaseMissing(User::TABLE_NAME, $formRequest);

        Event::fake([
            CreateUserBroadcast::class
        ]);

        $this->postJson(route('body-shop.users.store'), $formRequest + ['role_id' => $roleId])
            ->assertCreated();

        Event::assertDispatched(CreateUserBroadcast::class);

        $this->assertDatabaseHas(User::TABLE_NAME, $formRequest);
    }

    public function test_bs_s_admin_can_create_mechanic(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $formRequest = [
            'first_name' => 'FName',
            'last_name' => 'LName',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
        ];

        $roleId = $this->getRoleRepository()->findByName(User::BSMECHANIC_ROLE)->id;

        $this->assertDatabaseMissing(User::TABLE_NAME, $formRequest);

        Event::fake([
            CreateUserBroadcast::class
        ]);

        $this->postJson(route('body-shop.users.store'), $formRequest + ['role_id' => $roleId])
            ->assertCreated();

        Event::assertDispatched(CreateUserBroadcast::class);

        $this->assertDatabaseHas(User::TABLE_NAME, $formRequest);
    }

    public function test_bs_s_admin_cant_create_superadmin(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $formRequest = [
            'first_name' => 'FName',
            'last_name' => 'LName',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
        ];

        $roleId = $this->getRoleRepository()->findByName(User::BSSUPERADMIN_ROLE)->id;

        $this->assertDatabaseMissing(User::TABLE_NAME, $formRequest);

        Event::fake([
            CreateUserBroadcast::class
        ]);

        $this->postJson(route('body-shop.users.store'), $formRequest + ['role_id' => $roleId])
            ->assertForbidden();

        $this->assertDatabaseMissing(User::TABLE_NAME, $formRequest);
    }

    public function test_bs_admin_can_create_mechanic(): void
    {
        $this->loginAsBodyShopAdmin();

        $formRequest = [
            'first_name' => 'FName',
            'last_name' => 'LName',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
        ];

        $roleId = $this->getRoleRepository()->findByName(User::BSMECHANIC_ROLE)->id;

        $this->assertDatabaseMissing(User::TABLE_NAME, $formRequest);

        Event::fake([
            CreateUserBroadcast::class
        ]);

        $this->postJson(route('body-shop.users.store'), $formRequest + ['role_id' => $roleId])
            ->assertCreated();

        Event::assertDispatched(CreateUserBroadcast::class);

        $this->assertDatabaseHas(User::TABLE_NAME, $formRequest);
    }

    public function test_bs_admin_cant_create_superadmin_and_admin(): void
    {
        $this->loginAsBodyShopAdmin();

        $formRequest = [
            'first_name' => 'FName',
            'last_name' => 'LName',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
        ];

        $roleId = $this->getRoleRepository()->findByName(User::BSSUPERADMIN_ROLE)->id;

        $this->assertDatabaseMissing(User::TABLE_NAME, $formRequest);

        $this->postJson(route('body-shop.users.store'), $formRequest + ['role_id' => $roleId])
            ->assertForbidden();

        $this->assertDatabaseMissing(User::TABLE_NAME, $formRequest);

        $roleId = $this->getRoleRepository()->findByName(User::BSADMIN_ROLE)->id;

        $this->assertDatabaseMissing(User::TABLE_NAME, $formRequest);

        $this->postJson(route('body-shop.users.store'), $formRequest + ['role_id' => $roleId])
            ->assertForbidden();

        $this->assertDatabaseMissing(User::TABLE_NAME, $formRequest);
    }
}
