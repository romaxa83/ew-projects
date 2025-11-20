<?php

namespace Tests\Feature\Mutations\BackOffice\Permission;

use App\GraphQL\Mutations\BackOffice\Permission\AdminRoleDeleteMutation;
use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use App\Permissions\Departments\CreatePermission;
use App\Permissions\Roles\RoleDeletePermission;
use App\Permissions\Roles\RoleListPermission;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

class AdminRoleDeleteMutationTest extends TestCase
{
    use RoleHelperHelperTrait;

    public const MUTATION = AdminRoleDeleteMutation::NAME;

    public function test_not_auth_user_cant_delete_admin_role(): void
    {
        $role = $this->generateAdminRole();

        $query = sprintf(
            'mutation { %s ( id: %s ) }',
            self::MUTATION,
            $role->id,
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);
        $this->assertGraphQlUnauthorized($result);
    }

    protected function generateAdminRole(): Role
    {
        return $this->generateRole('Role1', [RoleDeletePermission::KEY], Admin::GUARD);
    }

    public function test_a_simple_admin_cant_create_new_admin_role(): void
    {
        $this->loginAsAdmin();

        $this->test_not_auth_user_cant_delete_admin_role();
    }

    public function test_a_permitted_admin_can_delete_admin_role(): void
    {
        $role = $this->generateRole('Role for Deleting', [RoleDeletePermission::KEY], Admin::GUARD);
        $admin = $this->loginAsSuperAdmin();
        $admin->assignRole($role);

        $roleToDelete = $this->generateRole('Role1', [CreatePermission::KEY], Admin::GUARD);

        $adminWithRole = Admin::factory()->create();
        $adminWithRole->assignRole($roleToDelete);

        $query = sprintf(
            'mutation { %s ( id: %s ) }',
            self::MUTATION,
            $roleToDelete->id,
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);

        self::assertExceptionMessage($result, __("exceptions.roles.cant_delete_role_attach_user"));

        $permissionsDetached = !$roleToDelete->permissions()->exists();

        self::assertFalse($permissionsDetached);
    }

    /** @test */
    public function cant_delete_admin_role(): void
    {
        $admin = $this->loginAsSuperAdmin();

        $query = sprintf(
            'mutation { %s ( id: %s ) }',
            self::MUTATION,
            $admin->roles[0]->id,
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);

        self::assertExceptionMessage($result, __("exceptions.roles.cant_delete_role"));
    }

    public function test_it_get_validation_error_on_wrong_id(): void
    {
        $role = $this->generateRole('Role1', [RoleDeletePermission::KEY], Admin::GUARD);
        $admin = $this->loginAsAdmin();
        $admin->assignRole($role);

        $query = sprintf(
            'mutation { %s ( id: %s ) }',
            self::MUTATION,
            9999999,
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);
        $this->assertResponseHasValidationMessage(
            $result,
            'id',
            ['validation.role_id_is_not_exists_or_wrong_guard_scope']
        );
    }

    public function test_it_get_validation_error_on_id_with_different_guard_scope(): void
    {
        $role = $this->generateRole('Role1', [RoleDeletePermission::KEY], Admin::GUARD);
        $admin = $this->loginAsAdmin();
        $admin->assignRole($role);

        $employeeRoleName = 'Employee Role';
        $employeeRole = $this->generateRole($employeeRoleName, [RoleListPermission::KEY]);

        $query = sprintf(
            'mutation { %s ( id: %s ) }',
            self::MUTATION,
            $employeeRole->id
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);
        $this->assertResponseHasValidationMessage(
            $result,
            'id',
            ['validation.role_id_is_not_exists_or_wrong_guard_scope']
        );
    }
}
