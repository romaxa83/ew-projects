<?php

namespace Tests\Feature\Mutations\BackOffice\Permission;

use App\GraphQL\Mutations\BackOffice\Permission\UserRoleUpdateMutation;
use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use App\Permissions\Roles\RoleListPermission;
use App\Permissions\Roles\RoleUpdatePermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class UserRoleUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const MUTATION = UserRoleUpdateMutation::NAME;

    public function test_a_simple_user_cant_update_employee_role(): void
    {
        $this->loginAsUser();

        $this->test_not_auth_user_cant_update_employee_role();
    }

    public function test_not_auth_user_cant_update_employee_role(): void
    {
        $employeeRole = $this->generateEmployeeRole();
        $translations = '';
        foreach (languages() as $language) {
            $translations .= '{language: "'.$language->slug.'", title: "some name '.$language->slug.'"},';
        }
        $translations = trim($translations, ',');

        $query = sprintf(
            'mutation { %s (
                        id: "%s"
                        name: "some name"
                        translations: [%s]
                        permissions: [%s]
                    ) { id translations {id title language} } }',
            self::MUTATION,
            $employeeRole->id,
            $translations,
            '"role.list"'

        );

        $result = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk();

        $this->assertGraphQlUnauthorized($result);
    }

    protected function generateEmployeeRole(): Role
    {
        return $this->generateRole('Employee role', [RoleListPermission::KEY]);
    }

    public function test_a_simple_admin_cant_update_employee_role(): void
    {
        $this->loginAsAdmin();

        $this->test_not_auth_user_cant_update_employee_role();
    }

    public function test_a_permitted_admin_can_update_employee_role(): void
    {
        $updateRoleRole = $this->generateRole(
            'new role',
            [RoleUpdatePermission::KEY],
            Admin::GUARD
        );
        $admin = $this->loginAsAdmin();
        $admin->assignRole($updateRoleRole);

        $roleToUpdate = $this->generateEmployeeRole();

        $newNameForRole = 'Updated name';

        $translations = '';
        foreach (languages() as $language) {
            $translations .= '{language: "'.$language->slug.'", title: "'.$newNameForRole.'"},';
        }
        $translations = trim($translations, ',');


        $query = sprintf(
            'mutation { %s (
                        id: "%s"
                        name: "%s"
                        translations: [%s]
                        permissions: [%s]
                    ) { id  permissions { name } translations {id title language } } }',
            self::MUTATION,
            $roleToUpdate->id,
            $newNameForRole,
            $translations,

            '"role.list"'
        );

        $result = $this->postGraphQLBackOffice(compact('query'));
        $updatedRole = $result->json('data.' . self::MUTATION);

        self::assertEquals($newNameForRole, $updatedRole['translations'][0]['title'] ?? null);
        $permissions = $updatedRole['permissions'];

        $permission = $permissions[0] ?? null;

        self::assertEquals(RoleListPermission::KEY, $permission['name'] ?? null);
    }

    public function test_it_get_validation_error_on_wrong_id(): void
    {
        $role = $this->generateRole('Role1', [RoleUpdatePermission::KEY], Admin::GUARD);
        $admin = $this->loginAsAdmin();
        $admin->assignRole($role);

        $translations = '';
        foreach (languages() as $language) {
            $translations .= '{language: "'.$language->slug.'", title: "some name '.$language->slug.'"},';
        }
        $translations = trim($translations, ',');

        $query = sprintf(
            'mutation { %s (
                        id: %s
                        name: "some name"
                        translations: [%s]
                        permissions: [%s]
                  ) { id permissions { name } } }',
            self::MUTATION,
            9999999,
            $translations,
            '"role.list"'
        );

        $result = $this->postGraphQLBackOffice(compact('query'));
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

        $translations = '';
        foreach (languages() as $language) {
            $translations .= '{language: "'.$language->slug.'", title: "some name '.$language->slug.'"},';
        }
        $translations = trim($translations, ',');

        $adminRoleName = 'Admin Role';
        $adminRole = $this->generateRole($adminRoleName, [RoleListPermission::KEY], Admin::GUARD);

        $query = sprintf(
            'mutation { %s (
                        id: %s
                        name: "some name"
                        translations: [%s]
                        permissions: [%s]
                  ) { id permissions { name } } }',
            self::MUTATION,
            $adminRole->id,
            $translations,
            '"role.list"'
        );

        $result = $this->postGraphQLBackOffice(compact('query'));
        $this->assertResponseHasValidationMessage(
            $result,
            'id',
            ['validation.role_id_is_not_exists_or_wrong_guard_scope']
        );
    }

}
