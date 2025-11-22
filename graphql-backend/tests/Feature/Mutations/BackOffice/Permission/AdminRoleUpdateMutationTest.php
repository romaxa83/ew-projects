<?php

namespace Tests\Feature\Mutations\BackOffice\Permission;

use App\GraphQL\Mutations\BackOffice\Permission\AdminRoleUpdateMutation;
use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use App\Permissions\Companies\CompanyUpdatePermission;
use App\Permissions\Roles\RoleCreatePermission;
use App\Permissions\Roles\RoleListPermission;
use App\Permissions\Roles\RoleUpdatePermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

class AdminRoleUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperHelperTrait;

    public const MUTATION = AdminRoleUpdateMutation::NAME;

    public function test_a_simple_user_cant_create_new_admin_role(): void
    {
        $this->loginAsUser();

        $this->test_not_auth_user_cant_update_admin_role();
    }

    public function test_not_auth_user_cant_update_admin_role(): void
    {
        $role = $this->generateAdminRole();

        $translates = '';
        foreach (languages() as $language) {
            $translates .= '{language: "' . $language->slug . '", title: "some name ' . $language->slug . '"},';
        }
        $translates = trim($translates, ',');

        $query = sprintf(
            'mutation { %s (
                        id: "%s"
                        name: "some name"
                        translates: [%s]
                        permissions: ["%s"]
                    ) { id translates {id title language} } }',
            self::MUTATION,
            $role->id,
            $translates,
            CompanyUpdatePermission::KEY
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);
        $this->assertGraphQlUnauthorized($result);
    }

    protected function generateAdminRole(): Role
    {
        return $this->generateRole('Admin Role', [RoleCreatePermission::KEY], Admin::GUARD);
    }

    public function test_a_simple_admin_cant_create_new_admin_role(): void
    {
        $this->loginAsAdmin();

        $this->test_not_auth_user_cant_update_admin_role();
    }

    public function test_a_permitted_admin_can_create_new_admin_role(): void
    {
        $role = $this->generateRole('Role1', [RoleUpdatePermission::KEY], Admin::GUARD);
        $admin = $this->loginAsAdmin();
        $admin->assignRole($role);

        $updatingRole = $this->generateAdminRole();

        $updatingRoleName = 'updated Role name';

        $translates = '';
        foreach (languages() as $language) {
            $translates .= '{language: "' . $language->slug . '", title: "' . $updatingRoleName . '"},';
        }
        $translates = trim($translates, ',');


        $query = sprintf(
            'mutation { %s (
                        id: "%s"
                        name: "%s"
                        translates: [%s]
                        permissions: ["%s"]
                    ) { id  permissions { name } translates {id title language } } }',
            self::MUTATION,
            $updatingRole->id,
            $updatingRoleName,
            $translates,
            CompanyUpdatePermission::KEY
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);
        $updatedRole = $result->json('data.' . AdminRoleUpdateMutation::NAME);

        self::assertEquals($updatingRoleName, $updatedRole['translates'][0]['title']);
        $permissions = $updatedRole['permissions'];
        $permission = array_shift($permissions);

        self::assertEquals(CompanyUpdatePermission::KEY, $permission['name']);
    }

    public function test_it_get_validation_error_on_wrong_id(): void
    {
        $role = $this->generateRole('Role1', [RoleUpdatePermission::KEY], Admin::GUARD);
        $admin = $this->loginAsAdmin();
        $admin->assignRole($role);

        $translates = '';
        foreach (languages() as $language) {
            $translates .= '{language: "' . $language->slug . '", title: "some name ' . $language->slug . '"},';
        }
        $translates = trim($translates, ',');

        $query = sprintf(
            'mutation { %s (
                        id: %s
                        name: "Some name"
                        translates: [%s]
                        permissions: ["%s"]
                  ) { id permissions { name } } }',
            self::MUTATION,
            9999999,
            $translates,
            RoleListPermission::KEY
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
        $role = $this->generateRole('Role1', [RoleUpdatePermission::KEY], Admin::GUARD);
        $admin = $this->loginAsAdmin();
        $admin->assignRole($role);

        $translates = '';
        foreach (languages() as $language) {
            $translates .= '{language: "' . $language->slug . '", title: "some name ' . $language->slug . '"},';
        }
        $translates = trim($translates, ',');

        $employeeRoleName = 'Employee Role';
        $employeeRole = $this->generateRole($employeeRoleName, [RoleListPermission::KEY]);

        $query = sprintf(
            'mutation { %s (
                        id: %s
                        name: "Some title"
                        translates: [%s]
                        permissions: ["%s"]
                  ) { id permissions { name } } }',
            self::MUTATION,
            $employeeRole->id,
            $translates,
            RoleListPermission::KEY
        );

        $result = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk();
        $this->assertResponseHasValidationMessage(
            $result,
            'id',
            ['validation.role_id_is_not_exists_or_wrong_guard_scope']
        );
    }
}
