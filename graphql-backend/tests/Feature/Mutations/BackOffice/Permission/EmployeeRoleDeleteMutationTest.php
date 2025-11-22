<?php

namespace Tests\Feature\Mutations\BackOffice\Permission;

use App\GraphQL\Mutations\BackOffice\Permission\EmployeeRoleDeleteMutation;
use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use App\Models\Users\User;
use App\Permissions\Roles\RoleDeletePermission;
use App\Permissions\Roles\RoleListPermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

class EmployeeRoleDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperHelperTrait;

    public const MUTATION = EmployeeRoleDeleteMutation::NAME;

    public function test_a_simple_user_cant_delete_employee_role(): void
    {
        $this->loginAsUser();

        $this->test_not_auth_user_cant_delete_employee_role();
    }

    public function test_not_auth_user_cant_delete_employee_role(): void
    {
        $employeeRole = $this->generateEmployeeRole();

        $query = sprintf(
            'mutation { %s (  id: %s )}',
            self::MUTATION,
            $employeeRole->id,
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);
        $this->assertGraphQlUnauthorized($result);
    }

    protected function generateEmployeeRole(): Role
    {
        return $this->generateRole('Employee role', [RoleListPermission::KEY]);
    }

    public function test_a_simple_admin_cant_delete_employee_role(): void
    {
        $this->loginAsAdmin();

        $this->test_not_auth_user_cant_delete_employee_role();
    }

    public function test_a_permitted_admin_can_delete_employee_role(): void
    {
        $updateRoleRole = $this->generateRole('new role', [RoleDeletePermission::KEY], Admin::GUARD);
        $admin = $this->loginAsAdmin();
        $admin->assignRole($updateRoleRole);

        $roleToDelete = $this->generateEmployeeRole();

        $employee = User::factory()->create();
        $employee->assignRole($roleToDelete);

        $query = sprintf(
            'mutation { %s ( id: "%s" ) }',
            self::MUTATION,
            $roleToDelete->id
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);
        self::assertTrue($result->json('data.' . self::MUTATION));

        $permissionsDetached = !$roleToDelete->permissions()->exists();
        self::assertTrue($permissionsDetached);
    }

    public function test_it_get_validation_error_on_wrong_id(): void
    {
        $role = $this->generateRole('Role1', [RoleDeletePermission::KEY], Admin::GUARD);
        $admin = $this->loginAsAdmin();
        $admin->assignRole($role);

        $query = sprintf(
            'mutation { %s ( id: %s ) }',
            self::MUTATION,
            9999999
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
        $this->loginAsRoleEditor();

        $adminRoleName = 'Admin Role';
        $adminRole = $this->generateRole($adminRoleName, [RoleListPermission::KEY], Admin::GUARD);

        $query = sprintf(
            'mutation { %s ( id: %s ) }',
            self::MUTATION,
            $adminRole->id
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);
        $this->assertResponseHasValidationMessage(
            $result,
            'id',
            ['validation.role_id_is_not_exists_or_wrong_guard_scope']
        );
    }

    protected function loginAsRoleEditor(): void
    {
        $this->loginAsAdmin()->assignRole(
            $this->generateRole('Role1', [RoleDeletePermission::KEY], Admin::GUARD)
        );
    }

    public function test_cant_delete_role_for_owner(): void
    {
        $this->loginAsRoleEditor();

        $role = Role::factory()
            ->asDefault()
            ->create();

        $query = sprintf(
            'mutation { %s ( id: %s ) }',
            self::MUTATION,
            $role->id
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])
            ->assertOk();

        $this->assertServerError($result, __('exceptions.roles.cant-delete-role-for-owner'));
    }
}
