<?php

namespace Tests\Feature\Mutations\BackOffice\Admins;

use App\GraphQL\Mutations\BackOffice\Admins\AdminUpdateMutation;
use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use App\Permissions\Admins\AdminUpdatePermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class AdminUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const MUTATION = AdminUpdateMutation::NAME;

    protected array $data = [];

    public function test_cant_update_admin_by_simple_user(): void
    {
        $this->loginAsUser();

        $this->test_cant_update_admin_by_not_auth_user();
    }

    public function test_cant_update_admin_by_not_auth_user(): void
    {
        $updatingAdmin = Admin::factory()->create();

        $query = sprintf(
            'mutation { %s (id: %s name: "%s" email: "%s" password: "%s" role_id: %s) { id name email } }',
            self::MUTATION,
            $updatingAdmin->id,
            'Update Admin Name',
            'new.admin.email@example.com',
            'password',
            $this->data['role']->id
        );

        $result = $this->postGraphQLBackOffice(compact('query'));
        $this->assertGraphQlUnauthorized($result);
    }

    public function test_cant_update_admin_by_not_permitted_admin(): void
    {
        $this->loginAsAdmin();

        $this->test_cant_update_admin_by_not_auth_user();
    }

    public function test_a_permitted_admin_can_update_admin(): void
    {
        $this->loginAsSuperAdmin();
        $updatingAdmin = Admin::factory()->create();

        $updateAdminName = 'Update Admin Name';
        $updateAdminEmail = 'update.admin.email@example.com';

        $this->assertDatabaseMissing(
            Admin::TABLE,
            [
                'name' => $updateAdminName,
                'email' => $updateAdminEmail,
            ]
        );

        $query = sprintf(
            'mutation { %s (id: %s name: "%s" email: "%s" role_id: %s) { id name email } }',
            self::MUTATION,
            $updatingAdmin->id,
            $updateAdminName,
            $updateAdminEmail,
            $this->data['role']->id
        );

        $result = $this->postGraphQLBackOffice(compact('query'));
        $updatedAdmin = $result->json('data.' . self::MUTATION);

        self::assertNotNull($updatedAdmin['id']);
        self::assertEquals($updateAdminName, $updatedAdmin['name']);
        self::assertEquals($updateAdminEmail, $updatedAdmin['email']);

        $this->assertDatabaseHas(
            Admin::TABLE,
            [
                'id' => $updatingAdmin->id,
                'name' => $updateAdminName,
                'email' => $updateAdminEmail,
            ]
        );
    }

    protected function loginByAdminManager(): Admin
    {
        return $this->loginAsAdmin()
            ->assignRole(
                $this->generateRole('Admin manager', [AdminUpdatePermission::KEY], Admin::GUARD)
            );
    }

    public function test_it_has_not_unique_email_validation_message(): void
    {
        $existsAdminEmail = 'exists.admin.email@example.com';
        Admin::factory()->create(['email' => $existsAdminEmail]);
        $updatingAdmin = Admin::factory()->create();

        $this->loginByAdminManager();
        $newAdminName = 'New Admin Name';

        $query = sprintf(
            'mutation { %s (id:%s name: "%s" email: "%s" password: "%s" role_id: %s) { id name email } }',
            self::MUTATION,
            $updatingAdmin->id,
            $newAdminName,
            $existsAdminEmail,
            'password',
            $this->data['role']->id
        );

        $result = $this->postGraphQLBackOffice(compact('query'));
        $this->assertResponseHasValidationMessage(
            $result,
            'email',
            [__('validation.unique', ['attribute' => 'email'])],
        );
    }

    public function test_a_permitted_admin_can_change_admin_role(): void
    {
        $this->loginAsSuperAdmin();
        $updatingAdmin = Admin::factory()->create();
        $role1 = Role::factory()->admin()->create();
        $role2 = Role::factory()->admin()->create();
        $updatingAdmin->assignRole($role1);

        $this->assertDatabaseMissing(
            config('permission.table_names.model_has_roles'),
            [
                'role_id' => $role2->id,
                'model_id' => $updatingAdmin->id,
                'model_type' => 'admin'
            ]
        );

        $query = sprintf(
            'mutation { %s (id: %s name: "%s" email: "%s" role_id: %s) { id name email roles { id translation { title } } } }',
            self::MUTATION,
            $updatingAdmin->id,
            $updatingAdmin->name,
            $updatingAdmin->email,
            $role2->id
        );

        $result = $this->postGraphQLBackOffice(compact('query'));
        $updatedAdmin = $result->json('data.' . self::MUTATION);

        self::assertEquals(
            [
                [
                    'id' => $role2->id,
                    'translation' => [
                        'title' => $role2->translation->title
                    ]
                ]
            ],
            $updatedAdmin['roles']
        );

        $this->assertDatabaseHas(
            config('permission.table_names.model_has_roles'),
            [
                'role_id' => $role2->id,
                'model_type' => 'admin',
                'model_id' => $updatingAdmin->id
            ]
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'role' => Role::factory()->admin()->create()
        ];
    }
}
