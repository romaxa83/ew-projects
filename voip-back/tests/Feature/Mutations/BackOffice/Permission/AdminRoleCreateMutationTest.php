<?php

namespace Tests\Feature\Mutations\BackOffice\Permission;

use App\GraphQL\Mutations\BackOffice\Permission\AdminRoleCreateMutation;
use App\Models\Admins\Admin;
use App\Permissions\Departments\UpdatePermission;
use App\Permissions\Roles\RoleCreatePermission;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

class AdminRoleCreateMutationTest extends TestCase
{
    use RoleHelperHelperTrait;

    public const MUTATION = AdminRoleCreateMutation::NAME;

    public function test_not_auth_user_cant_create_new_admin_role(): void
    {
        $translates = '';
        foreach (languages() as $language) {
            $translates .= '{language: ' . $language->slug . ', title: "Some name ' . $language->slug . '"},';
        }
        $translates = trim($translates, ',');

        $query = sprintf(
            'mutation { %s (
                        name: "Some name"
                        translations: [%s]
                        permissions: ["%s"]
                    ) { id } }',
            self::MUTATION,
            $translates,
            UpdatePermission::KEY
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);
        $this->assertGraphQlUnauthorized($result);
    }

    public function test_a_simple_admin_cant_create_new_admin_role(): void
    {
        $this->loginAsAdmin();

        $this->test_not_auth_user_cant_create_new_admin_role();
    }

    public function test_a_permitted_admin_can_create_new_admin_role(): void
    {
        $role = $this->generateRole('Role1', [RoleCreatePermission::KEY], Admin::GUARD);
        $admin = $this->loginAsSuperAdmin();
        $admin->assignRole($role);

        $translates = '';
        foreach (languages() as $language) {
            $translates .= '{language: ' . $language->slug . ', title: "some name ' . $language->slug . '"},';
        }
        $translates = trim($translates, ',');

        $query = sprintf(
            'mutation { %s (
                        name: "Some name"
                        translations: [%s]
                        permissions: ["%s"]
                    ) { id name permissions {id name} translations{id title language} } }',
            self::MUTATION,
            $translates,
            UpdatePermission::KEY
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])
            ->assertOk();
        $newRole = $result->json('data.' . AdminRoleCreateMutation::NAME);

        self::assertCount(languages()->count(), $newRole['translations']);

        $permissions = $newRole['permissions'];
        $permission = array_shift($permissions);

        self::assertEquals(UpdatePermission::KEY, $permission['name']);
    }
}
