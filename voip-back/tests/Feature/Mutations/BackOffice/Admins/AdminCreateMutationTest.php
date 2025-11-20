<?php

namespace Tests\Feature\Mutations\BackOffice\Admins;

use App\GraphQL\Mutations\BackOffice\Admins\AdminCreateMutation;
use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use App\Permissions\Admins\AdminCreatePermission;
use Illuminate\Support\Facades\App;
use Illuminate\Testing\TestResponse;
use Tests\Builders\Admins\AdminBuilder;
use Tests\Builders\Permissions\RoleBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperHelperTrait;
use Tests\Traits\ValidationErrors;

class AdminCreateMutationTest extends TestCase
{
    use RoleHelperHelperTrait;
    use ValidationErrors;

    protected RoleBuilder $roleBuilder;
    protected AdminBuilder $adminBuilder;

    public const MUTATION = AdminCreateMutation::NAME;

    protected array $data = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->roleBuilder = resolve(RoleBuilder::class);
        $this->adminBuilder = resolve(AdminBuilder::class);

        $this->data = [
            'name' => 'New Admin Name',
            'email' => 'new.admin.email@example.com',
            'password' => 'Password123',
            'role' => Role::factory()->admin()->create()
        ];
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

        return $this->postGraphQLBackOffice(['query' => $query]);
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

        $this->assertExceptionMessage($result, __('exceptions.admin.not_access'));
    }

    /** @test */
    public function can_create_new_admin_as_super_admin(): void
    {
        $this->loginAsSuperAdmin();

        $this->assertDatabaseMissing(
            Admin::TABLE,
            [
                'name' => $this->data['name'],
                'email' => $this->data['email'],
            ]
        );

        $result = $this->query();

        $createdAdmin = $result->json('data.' . self::MUTATION);

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

    public function test_it_returns_wrong_name_validation_message(): void
    {
        $this->loginByAdminManager();

        // with digit
        $this->data['name'] = 'test 1 name';
        $result = $this->query();
        $this->assertResponseHasValidationMessage(
            $result,
            'name',
            [__('validation.custom.name.name-rule', ['attribute' => __('validation.attributes.name')])]
        );

        // too short
        $this->data['name'] = 't';
        $result = $this->query();
        $this->assertResponseHasValidationMessage(
            $result,
            'name',
            [__('validation.custom.name.name-rule', ['attribute' => __('validation.attributes.name')])]
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
        $result = $this->query()
        ;

        $this->assertResponseHasValidationMessage(
            $result,
            'role_id',
            [
                $this->validationError('exists', 'role_id', locale: App::getLocale())
            ]
        );
    }

    /** @test*/
    public function fail_create_super_admin_if_it_exist(): void
    {
        $roleSuperAdmin = Role::query()->where('name', Role::superAdminName())->first();

        $this->loginAsSuperAdmin();
        $this->data['role'] = $roleSuperAdmin;

        $result = $this->query()
        ;

        $this->assertErrorMessage($result, __('exceptions.admin.cant_create_super_admin'));
    }

    protected function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    name: "%s"
                    email: "%s"
                    password: "%s"
                    role_id: %s
                ) {
                    id
                    name
                    email

                    relations {
                        id
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'name'),
            data_get($data, 'email'),
            data_get($data, 'password'),
            data_get($data, 'role_id'),
        );
    }
}
