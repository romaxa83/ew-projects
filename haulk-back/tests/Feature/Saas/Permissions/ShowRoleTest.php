<?php

namespace Tests\Feature\Saas\Permissions;

use App\Models\Admins\Admin;
use App\Permissions\Admins\AdminList;

class ShowRoleTest extends BaseRoleTest
{
    public function test_not_auth_admin_cant_show_role(): void
    {
        $role = $this->createRole('role 1', [AdminList::KEY], Admin::GUARD);

        $this->getJsonV1SaasRolesShow($role)
            ->assertUnauthorized();
    }

    public function test_not_permitted_admin_cant_show_role(): void
    {
        $this->loginAsSaasAdmin();

        $role = $this->createRole('role 1', [AdminList::KEY], Admin::GUARD);

        $this->getJsonV1SaasRolesShow($role)
            ->assertForbidden();
    }

    public function test_permitted_admin_cant_show_role_not_for_admins(): void
    {
        $this->loginAsSaasAdmin(
            $this->createAdminWithRoleShowPermission()
        );

        $role = $this->createRole('Role 1', [AdminList::KEY]);

        $this->getJsonV1SaasRolesShow($role)
            ->assertNotFound();
    }

    public function test_permitted_admin_can_show_role_not_for_admins(): void
    {
        $this->loginAsSaasAdmin(
            $this->createAdminWithRoleShowPermission()
        );

        $name = 'Role 1';
        $permissions = [AdminList::KEY];
        $role = $this->createRole($name, $permissions, Admin::GUARD);

        $roleArray = $this->getJsonV1SaasRolesShow($role)
            ->assertOk()
            ->json('data');

        self::assertEquals($name, $roleArray['name']);
        self::assertEquals($permissions, $roleArray['permissions']);
    }
}
