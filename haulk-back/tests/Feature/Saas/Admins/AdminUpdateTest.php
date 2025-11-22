<?php

namespace Tests\Feature\Saas\Admins;

use App\Models\Admins\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class AdminUpdateTest extends TestCase
{
    use DatabaseTransactions;
    use AdminFactory;
    use PermissionFactory;

    public function test_not_auth_admin_cant_update_other_admin(): void
    {
        $admin = $this->createAdmin();

        $newEmail = 'update_admin@email.com';

        $attrs = [
            'email' => $newEmail,
        ];

        $this->putJson(route('v1.saas.admins.update', $admin), $attrs)
            ->assertUnauthorized();
    }

    public function test_auth_admin_without_permission_cant_update_other_admin(): void
    {
        $this->loginAsSaasAdmin();

        $admin = $this->createAdmin();

        $newEmail = 'update_admin@email.com';

        $attrs = [
            'full_name' => $admin->full_name,
            'email' => $newEmail,
            'phone' => $admin->phone,
        ];

        $this->putJson(route('v1.saas.admins.update', $admin), $attrs)
            ->assertForbidden();
    }

    public function test_auth_admin_with_permission_cant_update_other_admin(): void
    {
        $updater = $this->createAdmin()->assignRole(
            $this->createRoleAdminManager()
        );

        $this->loginAsSaasAdmin($updater);

        $admin = $this->createAdmin();

        $newEmail = 'update_admin@email.com';

        $attrs = [
            'full_name' => $admin->full_name,
            'email' => $newEmail,
            'phone' => $admin->phone,
        ];

        $this->assertDatabaseHas(
            Admin::TABLE,
            [
                'id' => $admin->id,
                'email' => $admin->email,
            ]
        );

        $this->assertDatabaseMissing(Admin::TABLE, $attrs);

        $this->putJson(route('v1.saas.admins.update', $admin), $attrs)
            ->assertOk();

        $this->assertDatabaseHas(
            Admin::TABLE,
            [
                'id' => $admin->id,
                'email' => $newEmail,
            ]
        );

        $this->assertDatabaseMissing(
            Admin::TABLE,
            [
                'id' => $admin->id,
                'email' => $admin->email,
            ]
        );
    }

    public function test_admin_can_update_other_admin_full_name(): void
    {
        $updater = $this->createAdmin()->assignRole(
            $this->createRoleAdminManager()
        );

        $this->loginAsSaasAdmin($updater);

        $admin = $this->createAdmin();

        $newFullName = 'New Full Name';

        $attrs = [
            'full_name' => $newFullName,
            'email' => $admin->email,
            'phone' => $admin->phone,
        ];

        $this->assertDatabaseHas(
            Admin::TABLE,
            [
                'id' => $admin->id,
                'full_name' => $admin->full_name,
                'email' => $admin->email,
            ]
        );

        $this->assertDatabaseMissing(Admin::TABLE, $attrs);

        $this->putJson(route('v1.saas.admins.update', $admin), $attrs)
            ->assertOk();

        $this->assertDatabaseHas(
            Admin::TABLE,
            [
                'id' => $admin->id,
                'full_name' => $newFullName,
                'email' => $admin->email,
            ]
        );

        $this->assertDatabaseMissing(
            Admin::TABLE,
            [
                'id' => $admin->id,
                'full_name' => $admin->full_name,
                'email' => $admin->email,
            ]
        );
    }
}
