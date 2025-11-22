<?php

namespace Tests\Feature\Saas\Permissions;

use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use App\Models\Permissions\RoleHasPermission;
use App\Permissions\Admins\AdminCreate;
use App\Permissions\Admins\AdminDelete;
use App\Permissions\Admins\AdminUpdate;

class UpdateRoleTest extends BaseRoleTest
{
    public function test_not_auth_admin_cant_update_other_role(): void
    {
        $role = $this->createRole();

        $this->getPutJsonRouteV1SaasRoleUpdate($role)
            ->assertUnauthorized();
    }

    public function test_not_permitted_admin_cant_update_other_role(): void
    {
        $this->loginAsSaasAdmin();

        $role = $this->createRole();

        $attrs = [
            'name' => 'new role name',
            'permissions' => [
                AdminDelete::KEY,
            ]
        ];

        $this->getPutJsonRouteV1SaasRoleUpdate($role, $attrs)
            ->assertForbidden();
    }

    public function test_permitted_admin_can_update_other_role(): void
    {
        $this->loginAsSaasAdmin(
            $this->createAdminWithRoleUpdaterPermission()
        );

        $roleName = 'Role 1';
        $role = $this->createRole($roleName, [AdminCreate::KEY, AdminUpdate::KEY], Admin::GUARD);

        $newRoleName = 'Updated role 1';
        $attrs = [
            'name' => $newRoleName,
            'permissions' => [
                AdminCreate::KEY
            ],
        ];

        $this->assertDatabaseHas(
            Role::TABLE,
            [
                'id' => $role->id,
                'name' => $roleName
            ]
        );

        $this->assertDatabaseMissing(
            Role::TABLE,
            [
                'id' => $role->id,
                'name' => $newRoleName
            ]
        );

        $count = RoleHasPermission::query()
            ->where(['role_id' => $role->id])
            ->count();

        self::assertEquals(2, $count);

        $this->getPutJsonRouteV1SaasRoleUpdate($role, $attrs)
            ->assertOk();

        $this->assertDatabaseMissing(
            Role::TABLE,
            [
                'id' => $role->id,
                'name' => $roleName
            ]
        );

        $this->assertDatabaseHas(
            Role::TABLE,
            [
                'id' => $role->id,
                'name' => $newRoleName
            ]
        );

        $count = RoleHasPermission::query()
            ->where(['role_id' => $role->id])
            ->count();

        self::assertEquals(1, $count);
    }

}
