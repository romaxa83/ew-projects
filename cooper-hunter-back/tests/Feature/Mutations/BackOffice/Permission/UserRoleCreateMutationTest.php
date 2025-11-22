<?php

namespace Tests\Feature\Mutations\BackOffice\Permission;

use App\GraphQL\Mutations\BackOffice\Permission\UserRoleCreateMutation;
use App\Models\Admins\Admin;
use App\Permissions\Roles\RoleCreatePermission;
use App\Permissions\Roles\RoleListPermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class UserRoleCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const MUTATION = UserRoleCreateMutation::NAME;

    public function test_a_simple_user_cant_create_employee_role(): void
    {
        $this->loginAsUser();

        $this->test_not_auth_user_cant_create_employee_role();
    }

    public function test_not_auth_user_cant_create_employee_role(): void
    {
        $translations = '';
        foreach (languages() as $language) {
            $translations .= '{language: "'.$language->slug.'", title: "some name '.$language->slug.'"},';
        }
        $translations = trim($translations, ',');

        $query = sprintf(
            'mutation { %s (
                        name: "Some name"
                        translations: [%s]
                        permissions: [%s]
                    ){ id permissions {id name} translations{id title language} } }',
            self::MUTATION,
            $translations,
            '"role.list"'
        );
        $result = $this->postGraphQLBackOffice(compact('query'));
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

        $translations = '';
        foreach (languages() as $language) {
            $translations .= '{language: "'.$language->slug.'", title: "some name '.$language->slug.'"},';
        }
        $translations = trim($translations, ',');

        $query = sprintf(
            'mutation { %s (
                        name: "Some name"
                        translations: [%s]
                        permissions: [%s]
                    ) { id permissions {id name} translations{id title language} } }',
            self::MUTATION,
            $translations,
            '"role.list"'
        );

        $result = $this->postGraphQLBackOffice(compact('query'));
        $createdEmployeeRole = $result->json('data.' . self::MUTATION);

        self::assertCount(languages()->count(), $createdEmployeeRole['translations']);

        $permissions = $createdEmployeeRole['permissions'] ?? null;

        $permission = $permissions[0] ?? null;

        self::assertEquals(RoleListPermission::KEY, $permission['name'] ?? null);
    }


}
