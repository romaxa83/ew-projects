<?php

namespace Tests\Feature\Saas\Permissions;

use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use App\Permissions\Admins\AdminCreate;

class DeleteRoleTest extends BaseRoleTest
{
    public function test_not_auth_admin_cant_delete_role(): void
    {
        $role = $this->createRole();

        $this->getDeleteJsonV1SaasRolesDestroy($role)
            ->assertUnauthorized();
    }

    public function test_not_permitted_admin_cant_delete_role(): void
    {
        $this->loginAsSaasAdmin();

        $role = $this->createRole();

        $this->getDeleteJsonV1SaasRolesDestroy($role)
            ->assertForbidden();
    }

    public function test_permitted_admin_can_delete_role(): void
    {
        $this->loginAsSaasAdmin(
            $this->createAdminWithRoleDeleterPermission()
        );

        $role = $this->createRole('role name', [AdminCreate::KEY], Admin::GUARD);

        $this->assertDatabaseHas(Role::TABLE, ['id' => $role->id]);

        $this->getDeleteJsonV1SaasRolesDestroy($role)
            ->assertNoContent();

        $this->assertDatabaseMissing(Role::TABLE, ['id' => $role->id]);
    }

    public function test_permitted_admin_cant_delete_role_with_incorrect_guard_name(): void
    {
        $this->loginAsSaasAdmin(
            $this->createAdminWithRoleDeleterPermission()
        );

        $role = $this->createRole();

        $this->getDeleteJsonV1SaasRolesDestroy($role)
            ->assertNotFound();
    }
}
