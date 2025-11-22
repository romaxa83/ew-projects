<?php

namespace Tests\Feature\Saas\Permissions;

use App\Models\Admins\Admin;
use App\Permissions\Admins\AdminCreate;
use App\Permissions\Admins\AdminList;
use App\Permissions\Admins\AdminUpdate;
use App\Permissions\SimplePermission;

class IndexRoleTest extends BaseRoleTest
{
    public function test_not_auth_admin_cant_list_role(): void
    {
        $this->generateRoles();

        $this->getJsonV1SaasRolesIndex()
            ->assertUnauthorized();
    }

    private function generateRoles(): void
    {
        $this->createRole('Role 1', [AdminCreate::KEY], Admin::GUARD);
        $this->createRole('Role 2', [AdminUpdate::KEY], Admin::GUARD);
        $this->createRole('Role 3', [AdminList::KEY], Admin::GUARD);
    }

    public function test_not_permitted_admin_cant_list_role(): void
    {
        $this->loginAsSaasAdmin();

        $this->generateRoles();

        $this->getJsonV1SaasRolesIndex()
            ->assertForbidden();
    }

    public function test_permitted_admin_can_list_roles(): void
    {
        $this->loginAsSaasAdmin(
            $this->createAdminWithRoleListPermission()
        );

        $this->generateRoles();

        $roles = $this->getJsonV1SaasRolesIndex()
            ->assertOk()
            ->json('data');

        self::assertCount(5, $roles);

        $role = array_shift($roles);

        self::assertEquals([], $role['permission']);
    }

    public function test_permitted_admin_can_list_roles_check_permission_for_item(): void
    {
        $this->loginAsSaasAdmin(
            $this->createAdminWithRoleManagerPermissions()
        );

        $this->generateRoles();

        $roles = $this->getJsonV1SaasRolesIndex()
            ->assertOk()
            ->json('data');

        $role = array_shift($roles);
        self::assertEquals(
            [
                SimplePermission::SHOW,
                SimplePermission::UPDATE,
                SimplePermission::DELETE,
            ],
            $role['permission']
        );
    }
}
