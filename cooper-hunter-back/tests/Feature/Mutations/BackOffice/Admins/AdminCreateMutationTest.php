<?php

namespace Tests\Feature\Mutations\BackOffice\Admins;

use App\GraphQL\Mutations\BackOffice\Admins\AdminCreateMutation;
use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use App\Permissions\Admins\AdminCreatePermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;
use Tests\Traits\ValidationErrors;

class AdminCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;
    use ValidationErrors;

    public const MUTATION = AdminCreateMutation::NAME;

    protected array $data = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'name' => 'New Admin Name',
            'email' => 'new.admin.email@example.com',
            'password' => 'password1-1!',
            'role' => Role::factory()
                ->admin()
                ->create()
        ];

        Notification::fake();
    }

    public function test_cant_crate_new_admin_for_simple_user(): void
    {
        $this->loginAsUser();

        $this->test_cant_create_new_admin_for_not_auth_user();
    }

    public function test_cant_create_new_admin_for_not_auth_user(): void
    {
        $result = $this->query();

        $this->assertGraphQlUnauthorized($result);
    }

    protected function query(): TestResponse
    {
        $query = sprintf(
            'mutation { %s (name: "%s" email: "%s" password: "%s" role_id: %s) { id name email roles { id translation { title } } } }',
            self::MUTATION,
            $this->data['name'],
            $this->data['email'],
            $this->data['password'],
            $this->data['role']->id
        );

        return $this->postGraphQLBackOffice(compact('query'));
    }

    public function test_cant_create_new_admin_for_not_permitted_admin(): void
    {
        $this->loginAsAdmin();

        $this->test_cant_create_new_admin_for_not_auth_user();
    }

    public function test_a_permitted_admin_can_create_new_admin(): void
    {
        $this->loginByAdminManager();
        $this->assertDatabaseMissing(
            Admin::TABLE,
            [
                'name' => $this->data['name'],
                'email' => $this->data['email'],
            ]
        );

        $result = $this->query();
        $createdAdmin = $result->json('data.'.self::MUTATION);

        self::assertNotNull($createdAdmin['id']);
        self::assertEquals($this->data['name'], $createdAdmin['name']);
        self::assertEquals($this->data['email'], $createdAdmin['email']);
        self::assertEquals(
            [
                'id' => $this->data['role']->id,
                'translation' => [
                    'title' => $this->data['role']->translation->title
                ]
            ],
            $createdAdmin['roles'][0]
        );

        $this->assertDatabaseHas(
            Admin::TABLE,
            [
                'name' => $this->data['name'],
                'email' => $this->data['email'],
            ]
        );

        $this->assertDatabaseHas(
            config('permission.table_names.model_has_roles'),
            [
                'role_id' => $this->data['role']->id,
                'model_id' => $createdAdmin['id'],
                'model_type' => 'admin'
            ]
        );
    }

    protected function loginByAdminManager(): Admin
    {
        return $this->loginAsAdmin()
            ->assignRole(
                $this->generateRole('Admin manager', [AdminCreatePermission::KEY], Admin::GUARD)
            );
    }

    public function test_it_returns_wrong_password_validation_message(): void
    {
        $this->loginByAdminManager();
        $this->data['password'] = 'passwo';
        $result = $this->query();

        $this->assertResponseHasValidationMessage(
            $result,
            'password',
            [
                __('validation.custom.password.password-rule')
            ]
        );
    }

    public function test_it_has_not_unique_email_validation_message(): void
    {
        $existsAdminEmail = 'exists.admin.email@example.com';
        Admin::factory()->create(['email' => $existsAdminEmail]);

        $this->loginByAdminManager();
        $this->data['email'] = $existsAdminEmail;
        $result = $this->query();

        $this->assertResponseHasValidationMessage(
            $result,
            'email',
            [__('validation.unique', ['attribute' => 'email'])]
        );
    }

    public function test_it_has_validation_error_on_assigning_role_with_another_guard(): void
    {
        $this->loginByAdminManager();
        $this->data['role'] = Role::factory()->create();
        $result = $this->query();

        $this->assertResponseHasValidationMessage(
            $result,
            'role_id',
            [
                $this->validationError('exists', 'role_id')
            ]
        );
    }
}
