<?php

namespace Tests\Feature\Queries\BackOffice\Admins;

use App\Console\Commands\Admins\CreateAdminCommand;
use App\GraphQL\Queries\BackOffice\Admins\AdminsQuery;
use App\Models\Admins\Admin;
use App\Models\Permissions\Permission;
use App\Models\Permissions\Role;
use App\Permissions\Admins\AdminCreatePermission;
use App\Permissions\Admins\AdminDeletePermission;
use App\Permissions\Admins\AdminListPermission;
use App\Permissions\Admins\AdminUpdatePermission;
use App\ValueObjects\Email;
use Tests\Builders\Admins\AdminBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

class AdminQueryTest extends TestCase
{
    use RoleHelperHelperTrait;

    public const QUERY = AdminsQuery::NAME;
    public const COUNT = 3;

    protected AdminBuilder $adminBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->adminBuilder = resolve(AdminBuilder::class);
    }

    public function test_cant_get_list_of_admins_for_simple_user(): void
    {
        $this->loginAsEmployee();

        $this->test_cant_get_list_of_admins_for_not_permitted_user();
    }

    public function test_cant_get_list_of_admins_for_not_permitted_user(): void
    {
        Admin::factory()->times(self::COUNT)->create();

        $query = sprintf(
            'query { %s { data {id email}}}',
            self::QUERY
        );
        $result = $this->postGraphQLBackOffice(['query' => $query]);
        $this->assertGraphQlUnauthorized($result);
    }

    public function test_cant_get_list_of_admins_for_not_permitted_admin(): void
    {
        $this->loginAsAdmin();

        $this->test_cant_get_list_of_admins_for_not_permitted_user();
    }

    public function test_it_get_admin_list_for_permitted_admin(): void
    {
        $this->loginAsSuperAdmin();

        Admin::factory()->times(self::COUNT)->create();

        $query = sprintf(
            'query { %s { data {id email}}}',
            self::QUERY
        );
        $result = $this->postGraphQLBackOffice(['query' => $query])
        ;

        $admins = $result->json('data.' . self::QUERY . '.data');

        self::assertCount(self::COUNT + 1, $admins);
    }

    protected function loginAsAdminManager(): Admin
    {
        $admin = $this->loginAsAdmin();
        $admin->assignRole(
            $this->generateRole(
                'AdminManager',
                [
                    AdminListPermission::KEY,
                    AdminCreatePermission::KEY,
                    AdminUpdatePermission::KEY,
                    AdminDeletePermission::KEY
                ],
                Admin::GUARD
            )
        );

        return $admin;
    }

    public function test_can_filter_to_one_model_list_of_admins_by_email_chunk(): void
    {
        $this->loginAsSuperAdmin();

        $adminsEmail = 'admin.email@example.com';
        Admin::factory()->create(['email' => new Email($adminsEmail)]);
        Admin::factory()->times(self::COUNT)->create();

        $query = sprintf(
            'query { %s (query: "%s"){ data {id email}}}',
            self::QUERY,
            'admin.email'
        );

        $result = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk();

        $admins = $result->json('data.' . self::QUERY . '.data');
        self::assertCount(1, $admins);

        $admin = array_shift($admins);
        self::assertEquals($adminsEmail, $admin['email']);
    }

    public function test_it_show_list_of_user_for_created_super_admin_by_command(): void
    {
        $name = 'New admin name';
        $email = 'new.admin.email@example.com';
        $password = 'password';

        $this->artisan(CreateAdminCommand::class)
            ->expectsQuestion(CreateAdminCommand::QUESTION_NAME, $name)
            ->expectsQuestion(CreateAdminCommand::QUESTION_EMAIL, $email)
            ->expectsQuestion(CreateAdminCommand::QUESTION_PASSWORD, $password)
            ->assertExitCode(CreateAdminCommand::SUCCESS);

        $admin = Admin::query()->whereEmail($email)->first();

        $this->loginAsSuperAdmin($admin);

        Admin::factory()->times(self::COUNT)->create();

        $query = sprintf(
            'query { %s { data {id email}}}',
            self::QUERY
        );
        $result = $this->postGraphQLBackOffice(['query' => $query])
            ->assertOk();

        $admins = $result->json('data.' . self::QUERY . '.data');

        self::assertCount(self::COUNT + 1, $admins);
    }

    public function test_permitted_admin_can_get_admin_data_by_id(): void
    {
        $this->loginAsSuperAdmin();

        $admins = Admin::factory()->times(self::COUNT)->create();
        $admin = $admins->random();

        $query = sprintf(
            'query { %s (id: %s){ data { id name email } } }',
            self::QUERY,
            $admin->id
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);
        $adminsData = $result->json('data.' . self::QUERY . '.data');

        self::assertCount(1, $adminsData);

        $adminData = array_shift($adminsData);
        self::assertEquals($adminData, $admin->only(['id', 'name', 'email']));
    }

    public function test_permitted_admin_can_get_admin_data_with_role(): void
    {
        $this->loginAsSuperAdmin();

        $permissions = Permission::factory()
            ->count(self::COUNT)
            ->admin()
            ->create();

        $roles = Role::factory()
            ->count(self::COUNT)
            ->admin()
            ->create()
            ->each(
                function (Role $role) use ($permissions) {
                    $randomPermissions = $permissions->random(self::COUNT);
                    $role->permissions()->sync($randomPermissions);
                }
            );

        $admins = Admin::factory()
            ->times(self::COUNT)
            ->create()
            ->each(
                function (Admin $admin) use ($roles) {
                    $admin->roles()->sync($roles->random());
                }
            );

        $admin = $admins->random();

        $query = sprintf(
            'query { %s (id: %s){ data { id name email roles { id translation { title } permissions { id name } } } } }',
            self::QUERY,
            $admin->id
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);
        $adminsData = $result->json('data.' . self::QUERY . '.data');

        $adminArray = [
            'id' => $admin->id,
            'email' => (string)$admin->email,
            'name' => $admin->name,
            'roles' => [
                [
                    'id' => $admin->role->id,
                    'translation' => [
                        'title' => $admin->role->translation->title,
                    ],
                    'permissions' => $admin->role->permissions->map(
                        function (Permission $permission) {
                            return $permission->only(['id', 'name']);
                        }
                    )->all()
                ]
            ]
        ];

        self::assertEquals($adminArray, array_shift($adminsData));
    }

    public function test_sort_by_email(): void
    {
        $this->loginAsSuperAdmin();
        Admin::factory()->times(self::COUNT)->create();

        $query = sprintf(
            'query { %s (sort: "%s", per_page: %d) { data { id name email } } }',
            self::QUERY,
            'email-desc',
            self::COUNT
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])->assertOk();
        $data = collect($result->json('data.' . self::QUERY . '.data'));

        $this->assertEquals(
            $data->pluck('email'),
            $data->sortByDesc('email')->pluck('email')
        );
    }

    public function test_sort_by_name(): void
    {
        $this->loginAsSuperAdmin();
        Admin::factory()->times(self::COUNT)->create();

        $query = sprintf(
            'query { %s (sort: "%s", per_page: %d) { data { id name } } }',
            self::QUERY,
            'name-desc',
            self::COUNT
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])->assertOk();
        $data = collect($result->json('data.' . self::QUERY . '.data'));

        $this->assertEquals(
            $data->pluck('name'),
            $data->sortByDesc('name')->pluck('name')
        );
    }

    public function test_sort_by_created_at(): void
    {
        $this->loginAsSuperAdmin();
        Admin::factory()->times(self::COUNT)->create();

        $query = sprintf(
            'query { %s (sort: "%s", per_page: %d) { data { id name created_at } } }',
            self::QUERY,
            'created_at-desc',
            self::COUNT
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])->assertOk();
        $data = collect($result->json('data.' . self::QUERY . '.data'));

        $this->assertEquals(
            $data->pluck('created_at'),
            $data->sortByDesc('created_at')->pluck('created_at')
        );
    }

    public function test_sort_by_id(): void
    {
        $this->loginAsSuperAdmin();
        Admin::factory()->times(self::COUNT)->create();

        $query = sprintf(
            'query { %s (sort: "%s", per_page: %d) { data { id name created_at } } }',
            self::QUERY,
            'id-desc',
            self::COUNT
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])
            ->assertOk();
        $data = collect($result->json('data.' . self::QUERY . '.data'));

        $this->assertEquals(
            $data->pluck('id'),
            Admin::query()->orderByDesc('id')->limit(self::COUNT)->pluck('id')
        );

        $query = sprintf(
            'query { %s (sort: "%s", per_page: %d) { data { id name created_at } } }',
            self::QUERY,
            'id-asc',
            self::COUNT
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])
            ->assertOk();
        $data = collect($result->json('data.' . self::QUERY . '.data'));

        $this->assertEquals(
            $data->pluck('id'),
            Admin::query()->orderBy('id')->limit(self::COUNT)->pluck('id')
        );
    }

    /** @test */
    public function success_all_paginator_as_super_admin(): void
    {
        $this->loginAsSuperAdmin();

        $this->adminBuilder->create();
        $this->adminBuilder->create();
        $this->adminBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'meta' => [
                            'total' => 4
                        ],
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStr(): string
    {
        return sprintf(
            '
            {
                %s {
                    data {
                        id
                    }
                    meta {
                        total
                    }
                }
            }',
            self::QUERY
        );
    }
}
