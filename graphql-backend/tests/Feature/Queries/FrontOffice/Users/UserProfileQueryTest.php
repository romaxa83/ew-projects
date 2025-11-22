<?php

namespace Tests\Feature\Queries\FrontOffice\Users;

use App\GraphQL\Queries\FrontOffice\Users\UserProfileQuery;
use App\Models\Permissions\Permission;
use App\Permissions\Employees\EmployeeCreatePermission;
use App\Permissions\Employees\EmployeeUpdatePermission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

class UserProfileQueryTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperHelperTrait;

    public const QUERY = UserProfileQuery::NAME;

    public function test_it_get_profile_with_list_of_permissions(): void
    {
        /** @var Collection $permissions */
        $permissions = Permission::factory()
            ->count(5)
            ->create();
        $role = $this->generateRole(
            'test-role',
            $permissions->pluck('name')->all()
        );

        $user = $this->loginAsUser()->assignRole($role);

        $result = $this->query()
            ->assertOk();

        $profile = $result->json('data.' . self::QUERY);

        $permissionsArray = $permissions
            ->map(fn(Permission $permission) => $permission->only(['id', 'name']))
            ->all();

        self::assertEquals($user->id, $profile['id']);
        self::assertEquals($user->email, $profile['email']);
        self::assertEquals($user->first_name, $profile['first_name']);
        self::assertEquals($permissionsArray, $profile['permissions']);

        self::assertEquals(
            [
                'name' => $user->language->name,
                'slug' => $user->language->slug
            ],
            $profile['language']
        );
    }

    protected function query(): TestResponse
    {
        $query = sprintf(
            'query {
                %s {
                    id
                    first_name
                    last_name
                    middle_name
                    email
                    email_verified_at
                    lang
                    language {
                        name
                        slug
                    }
                    permissions {
                        id
                        name
                    }
                    company {
                        id
                    }
                }
            }',
            self::QUERY
        );

        return $this->postGraphQL(['query' => $query]);
    }

    public function test_it_get_filtered_permissions_for_company_without_active_package(): void
    {
        Config::set('grants.filter_enabled', true);

        /** @var Collection $permissions */
        $permissions = collect(
            [
                Permission::create(['name' => EmployeeCreatePermission::KEY]),
                $permission1 = Permission::create(['name' => EmployeeUpdatePermission::KEY]),
            ]
        );

        $role = $this->generateRole(
            'test-role',
            $permissions->pluck('name')->all()
        );

        $this->loginAsUser()->assignRole($role);

        $result = $this->query()
            ->assertOk();

        $permissionsArray = $result->json('data.' . self::QUERY . '.permissions');

        self::assertEquals(
            $permissions->map->only(['id', 'name'])->toArray(),
            $permissionsArray
        );
    }

    public function test_it_has_error_for_not_auth_users(): void
    {
        $result = $this->query()
            ->assertOk();

        $this->assertGraphQlUnauthorized($result);
    }
}
