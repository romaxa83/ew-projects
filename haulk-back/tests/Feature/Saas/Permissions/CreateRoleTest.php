<?php

namespace Tests\Feature\Saas\Permissions;

use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use App\Permissions\Admins\AdminCreate;
use App\Permissions\Admins\AdminDelete;
use App\Permissions\Admins\AdminList;
use App\Permissions\Admins\AdminUpdate;

class CreateRoleTest extends BaseRoleTest
{
    public function test_not_auth_admin_cant_create_grants(): void
    {
        $this->getPostJsonRouteV1SaasRolesStore($this->getArr())
            ->assertUnauthorized();
    }

    protected function getArr(): array
    {
        return [
            'name' => 'Role Name',
            'permissions' => [
                AdminCreate::KEY,
                AdminUpdate::KEY,
                AdminDelete::KEY,
                AdminList::KEY,
            ]
        ];
    }

    public function test_auth_admin_without_permissions_cant_create_grants(): void
    {
        $this->loginAsSaasAdmin();

        $this->getPostJsonRouteV1SaasRolesStore($this->getArr())
            ->assertForbidden();
    }

    public function test_permitted_admin_can_create_grants(): void
    {
        $creator = $this->createAdminWithRoleCreatorPermission();

        $this->loginAsSaasAdmin($creator);

        $attrs = $this->getArr();

        $this->assertDatabaseMissing(
            Role::TABLE,
            [
                'name' => $attrs['name'],
                'guard_name' => Admin::GUARD,
            ]
        );

        $this->getPostJsonRouteV1SaasRolesStore($attrs)
            ->assertCreated();

        $this->assertDatabaseHas(
            Role::TABLE,
            [
                'name' => $attrs['name'],
                'guard_name' => Admin::GUARD,
            ]
        );
    }

}
