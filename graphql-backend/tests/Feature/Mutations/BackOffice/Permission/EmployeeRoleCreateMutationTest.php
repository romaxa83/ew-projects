<?php

namespace Tests\Feature\Mutations\BackOffice\Permission;

use App\GraphQL\Mutations\BackOffice\Permission\EmployeeRoleCreateMutation;
use App\Models\Admins\Admin;
use App\Permissions\Roles\RoleCreatePermission;
use App\Permissions\Roles\RoleListPermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

class EmployeeRoleCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperHelperTrait;

    public const MUTATION = EmployeeRoleCreateMutation::NAME;

    public function test_a_simple_user_cant_create_employee_role(): void
    {
        $this->loginAsUser();

        $this->test_not_auth_user_cant_create_employee_role();
    }

    public function test_not_auth_user_cant_create_employee_role(): void
    {
        $translates = '';
        foreach (languages() as $language) {
            $translates .= '{language: "' . $language->slug . '", title: "some name ' . $language->slug . '"},';
        }
        $translates = trim($translates, ',');

        $query = sprintf(
            'mutation { %s (
                        name: "Some name"
                        translates: [%s]
                        permissions: [%s]
                    ){ id permissions {id name} translates{id title language} } }',
            self::MUTATION,
            $translates,
            '"role.list"'
        );
        $result = $this->postGraphQLBackOffice(['query' => $query]);
        $this->assertGraphQlUnauthorized($result);
    }

    public function test_a_simple_admin_cant_create_employee_role(): void
    {
        $this->loginAsAdmin();

        $this->test_not_auth_user_cant_create_employee_role();
    }

    public function test_a_permitted_admin_can_create_employee_role(): void
    {
        $createRoleRole = $this->generateRole(
            'new role',
            [RoleCreatePermission::KEY],
            Admin::GUARD
        );
        $admin = $this->loginAsAdmin();

        $admin->assignRole($createRoleRole);

        $translates = '';
        foreach (languages() as $language) {
            $translates .= '{language: "' . $language->slug . '", title: "some name ' . $language->slug . '"},';
        }
        $translates = trim($translates, ',');

        $query = sprintf(
            'mutation { %s (
                        name: "Some name"
                        translates: [%s]
                        permissions: [%s]
                    ) { id permissions {id name} translates{id title language} } }',
            self::MUTATION,
            $translates,
            '"role.list"'
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);
        $createdEmployeeRole = $result->json('data.' . self::MUTATION);

        self::assertCount(languages()->count(), $createdEmployeeRole['translates']);

        $permissions = $createdEmployeeRole['permissions'] ?? null;

        $permission = $permissions[0] ?? null;

        self::assertEquals(RoleListPermission::KEY, $permission['name'] ?? null);
    }


}
