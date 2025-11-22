<?php

namespace Tests\Feature\Mutations\BackOffice\Permission;

use App\GraphQL\Mutations\BackOffice\Permission\AdminRoleCreateMutation;
use App\Models\Admins\Admin;
use App\Permissions\Roles\RoleCreatePermission;
use App\Permissions\Users\UserUpdatePermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class AdminRoleCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const MUTATION = AdminRoleCreateMutation::NAME;

    public function test_a_simple_user_cant_create_new_admin_role(): void
    {
        $this->loginAsUser();

        $this->test_not_auth_user_cant_create_new_admin_role();
    }

    public function test_not_auth_user_cant_create_new_admin_role(): void
    {
        $translations = '';
        foreach (languages() as $language) {
            $translations .= '{language: "'.$language->slug.'", title: "Some name '.$language->slug.'"},';
        }
        $translations = trim($translations, ',');

        $query = sprintf(
            'mutation { %s (
                        name: "Some name"
                        translations: [%s]
                        permissions: ["%s"]
                    ) { id } }',
            self::MUTATION,
            $translations,
            UserUpdatePermission::KEY
        );

        $result = $this->postGraphQLBackOffice(compact('query'));
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
        $admin = $this->loginAsAdmin();
        $admin->assignRole($role);

        $translations = '';
        foreach (languages() as $language) {
            $translations .= '{language: "'.$language->slug.'", title: "some name '.$language->slug.'"},';
        }
        $translations = trim($translations, ',');

        $query = sprintf(
            'mutation { %s (
                        name: "Some name"
                        translations: [%s]
                        permissions: ["%s"]
                    ) { id name permissions {id name} translations{id title language} } }',
            self::MUTATION,
            $translations,
            UserUpdatePermission::KEY
        );

        $result = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk();
        $newRole = $result->json('data.'.AdminRoleCreateMutation::NAME);

        self::assertCount(languages()->count(), $newRole['translations']);

        $permissions = $newRole['permissions'];
        $permission = array_shift($permissions);

        self::assertEquals(UserUpdatePermission::KEY, $permission['name']);
    }
}
