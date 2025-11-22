<?php

namespace Tests\Feature\Api\BodyShop\Users;

use App\Broadcasting\Events\User\UpdateUserBroadcast;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class UserUpdateTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_not_update_user_for_unauthorized_users(): void
    {
        $user = $this->bsAdminFactory();

        $this->getJson(route('body-shop.users.update', $user))
            ->assertUnauthorized();
    }

    public function test_it_not_update_user_for_not_permitted_user(): void
    {
        $this->loginAsBodyShopMechanic();

        $user = $this->bsAdminFactory();

        $this->getJson(route('body-shop.users.update', $user))
            ->assertForbidden();
    }

    public function test_it_not_update_user_from_company(): void
    {
        $user = $this->dispatcherFactory();

        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.users.update', $user))
            ->assertNotFound();
    }

    public function test_s_admin_can_update_admin(): void
    {
        $user = $this->bsAdminFactory();

        $formRequest = [
            'first_name' => 'FName',
            'last_name' => 'LName',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
        ];

        $roleId = $this->getRoleRepository()->findByName(User::BSADMIN_ROLE)->id;

        $this->assertDatabaseMissing(User::TABLE_NAME, $formRequest);

        $this->loginAsBodyShopSuperAdmin();

        Event::fake([UpdateUserBroadcast::class]);

        $this->putJson(route('body-shop.users.update', $user->id), $formRequest + ['role_id' => $roleId])
            ->assertOk();

        Event::assertDispatched(UpdateUserBroadcast::class);

        $this->assertDatabaseHas(User::TABLE_NAME, $formRequest);
    }

    public function test_s_admin_can_update_mechanic(): void
    {
        $user = $this->bsAdminFactory();

        $formRequest = [
            'first_name' => 'FName',
            'last_name' => 'LName',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
        ];

        $roleId = $this->getRoleRepository()->findByName(User::BSMECHANIC_ROLE)->id;

        $this->assertDatabaseMissing(User::TABLE_NAME, $formRequest);

        $this->loginAsBodyShopSuperAdmin();

        Event::fake([UpdateUserBroadcast::class]);

        $this->putJson(route('body-shop.users.update', $user->id), $formRequest + ['role_id' => $roleId])
            ->assertOk();

        Event::assertDispatched(UpdateUserBroadcast::class);

        $this->assertDatabaseHas(User::TABLE_NAME, $formRequest);
    }

    public function test_s_admin_cant_update_s_admin(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $user = $this->bsSuperAdminFactory();

        $formRequest = [
            'first_name' => 'FName',
            'last_name' => 'LName',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
        ];

        $roleId = $this->getRoleRepository()->findByName(User::BSADMIN_ROLE)->id;

        $this->assertDatabaseMissing(User::TABLE_NAME, $formRequest);

        $this->putJson(route('body-shop.users.update', $user->id), $formRequest + ['role_id' => $roleId])
            ->assertForbidden();

        $this->assertDatabaseMissing(User::TABLE_NAME, $formRequest);

        $user = $this->bsAdminFactory();

        $roleId = $this->getRoleRepository()->findByName(User::BSSUPERADMIN_ROLE)->id;

        $this->putJson(route('body-shop.users.update', $user->id), $formRequest + ['role_id' => $roleId])
            ->assertForbidden();

        $this->assertDatabaseMissing(User::TABLE_NAME, $formRequest);
    }

    public function test_admin_can_update_mechanic(): void
    {
        $user = $this->bsMechanicFactory();

        $formRequest = [
            'first_name' => 'FName',
            'last_name' => 'LName',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
        ];

        $roleId = $this->getRoleRepository()->findByName(User::BSMECHANIC_ROLE)->id;

        $this->assertDatabaseMissing(User::TABLE_NAME, $formRequest);

        $this->loginAsBodyShopAdmin();

        Event::fake([UpdateUserBroadcast::class]);

        $this->putJson(route('body-shop.users.update', $user->id), $formRequest + ['role_id' => $roleId])
            ->assertOk();

        Event::assertDispatched(UpdateUserBroadcast::class);

        $this->assertDatabaseHas(User::TABLE_NAME, $formRequest);
    }

    public function test_admin_cant_update_s_admin_and_admin(): void
    {
        $this->loginAsBodyShopAdmin();

        $user = $this->bsSuperAdminFactory();

        $formRequest = [
            'first_name' => 'FName',
            'last_name' => 'LName',
            'email' => 'some.email@example.com',
            'phone' => '1-541-754-3010',
        ];

        $roleId = $this->getRoleRepository()->findByName(User::BSADMIN_ROLE)->id;

        $this->assertDatabaseMissing(User::TABLE_NAME, $formRequest);

        $this->putJson(route('body-shop.users.update', $user->id), $formRequest + ['role_id' => $roleId])
            ->assertForbidden();

        $this->assertDatabaseMissing(User::TABLE_NAME, $formRequest);

        $user = $this->bsAdminFactory();

        $roleId = $this->getRoleRepository()->findByName(User::BSADMIN_ROLE)->id;

        $this->putJson(route('body-shop.users.update', $user->id), $formRequest + ['role_id' => $roleId])
            ->assertForbidden();

        $this->assertDatabaseMissing(User::TABLE_NAME, $formRequest);
    }
}
