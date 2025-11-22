<?php

namespace Tests\Feature\Saas\Permissions;

use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use App\Permissions\Roles\RoleCreate;
use App\Permissions\Roles\RoleDelete;
use App\Permissions\Roles\RoleShow;
use App\Permissions\Roles\RoleUpdate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

abstract class BaseRoleTest extends TestCase
{
    use AdminFactory;
    use DatabaseTransactions;
    use PermissionFactory;

    protected function getPostJsonRouteV1SaasRolesStore(array $attrs = []): TestResponse
    {
        return $this->postJson(route('v1.saas.roles.store'), $attrs);
    }

    protected function getPutJsonRouteV1SaasRoleUpdate(Role $role, array $attrs = []): TestResponse
    {
        return $this->putJson(route('v1.saas.roles.update', $role), $attrs);
    }

    protected function getDeleteJsonV1SaasRolesDestroy(Role $role): TestResponse
    {
        return $this->deleteJson(route('v1.saas.roles.destroy', $role));
    }

    protected function getJsonV1SaasRolesIndex(): TestResponse
    {
        return $this->getJson(route('v1.saas.roles.index'));
    }

    protected function getJsonV1SaasRolesShow(Role $role): TestResponse
    {
        return $this->getJson(route('v1.saas.roles.show', $role));
    }

    protected function getJsonV1SaasAdminPermissions(): TestResponse
    {
        return $this->getJson(route('v1.saas.roles.permissions'));
    }

    protected function createAdminWithRoleManagerPermissions(): Admin
    {
        return $this->createAdmin()->assignRole(
            $this->createRoleManagerRole()
        );
    }

    protected function createAdminWithRoleListPermission(): Admin
    {
        return $this->createAdmin()->assignRole(
            $this->createRoleWithRoleListPermission()
        );
    }

    protected function createAdminWithRoleCreatorPermission(): Admin
    {
        return $this->createAdmin()->assignRole(
            $this->createRole('Role creator', [RoleCreate::KEY], Admin::GUARD)
        );
    }

    protected function createAdminWithRoleUpdaterPermission(): Admin
    {
        return $this->createAdmin()->assignRole(
            $this->createRole('Role updater', [RoleUpdate::KEY], Admin::GUARD)
        );
    }

    protected function createAdminWithRoleDeleterPermission(): Admin
    {
        return $this->createAdmin()->assignRole(
            $this->createRole('Role updater', [RoleDelete::KEY], Admin::GUARD)
        );
    }

    protected function createAdminWithRoleShowPermission(): Admin
    {
        return $this->createAdmin()->assignRole(
            $this->createRole('Role shower', [RoleShow::KEY], Admin::GUARD)
        );
    }

}
