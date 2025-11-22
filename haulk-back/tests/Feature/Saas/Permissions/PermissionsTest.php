<?php

namespace Tests\Feature\Saas\Permissions;

class PermissionsTest extends BaseRoleTest
{
    public function test_not_aut_admin_cant_list_permission_matrix(): void
    {
        $this->getJsonV1SaasAdminPermissions()
            ->assertUnauthorized();
    }

    public function test_not_permitted_admin_cant_list_permissions(): void
    {
        $this->loginAsSaasAdmin();

        $this->getJsonV1SaasAdminPermissions()
            ->assertForbidden();
    }

    public function test_permitted_admin_can_list_permissions(): void
    {
        $this->loginAsSaasAdmin(
            $this->createAdminWithRoleShowPermission()
        );

        $permissionGroups = $this->getJsonV1SaasAdminPermissions()
            ->assertOk()
            ->json('data');

        $permissionGroups = collect($permissionGroups);

        $group = $permissionGroups->shift();
        self::assertEquals('Roles', $group['name']);
        self::assertEquals('role', $group['key']);
    }
}
