<?php

namespace Tests\Feature\Queries\FrontOffice\Employees;

use App\GraphQL\Queries\FrontOffice\Employees\EmployeesQueryForCompany;
use App\Models\Companies\Company;
use App\Models\Permissions\Role;
use App\Models\Users\User;
use App\ValueObjects\Email;
use Core\GraphQL\Fields\BasePermissionField;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\EmployeesManagerHelperTrait;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

class EmployeesQueryForCompanyTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperHelperTrait;
    use EmployeesManagerHelperTrait;

    public const QUERY = EmployeesQueryForCompany::NAME;

    public function test_cant_get_list_of_employees_for_simple_user(): void
    {
        $this->loginAsUser();

        $this->test_cant_get_list_of_employees();
    }

    public function test_cant_get_list_of_employees(): void
    {
        $query = sprintf(
            'query { %s { data { first_name } } }',
            self::QUERY
        );
        $result = $this->postGraphQL(['query' => $query]);
        $this->assertGraphQlUnauthorized($result);
    }

    public function test_user_with_correct_permission_can_see_a_list_of_employees(): void
    {
        $manager = $this->loginAsEmployeesManager();
        $this->createUsers($manager->company);

        $query = sprintf(
            'query { %s (per_page: %s) { data { first_name email } } }',
            self::QUERY,
            50
        );
        $result = $this->postGraphQL(compact('query'))
            ->assertOk();

        $users = $result->json('data.' . self::QUERY . '.data');

        self::assertCount(21, $users);
    }

    protected function createUsers(Company $company, int $number = 20): Collection
    {
        $users = User::factory()->times($number)->create();

        $company->users()->attach($users);

        return $users;
    }

    public function test_can_sort_by_roles(): void
    {
        $manager = $this->loginAsEmployeesManager();
        $this->createUsers($manager->company);

        $query = sprintf(
            'query { %s (sort: "%s") { data { first_name email roles {translate {title}}} } }',
            self::QUERY,
            'roles-desc'
        );

        $result = $this->postGraphQL(['query' => $query])->assertOk();
        $data = collect($result->json('data.' . self::QUERY . '.data'));

        $this->assertEquals(
            $data->pluck('roles.*.translate.title'),
            $data->sortByDesc('roles.*.translate.title')->pluck('roles.*.translate.title')
        );
    }

    public function test_can_sort_by_sip_number(): void
    {
        $manager = $this->loginAsEmployeesManager();
        $this->createUsers($manager->company);

        $query = sprintf(
            'query { %s (sort: "%s") { data { sip_users {sip_number} } } }',
            self::QUERY,
            'sip_number-desc'
        );

        $result = $this->postGraphQL(['query' => $query])->assertOk();
        $data = collect($result->json('data.' . self::QUERY . '.data'));

        $this->assertEquals(
            $data->pluck('sip_users.*.sip_number'),
            $data->sortByDesc('sip_users.*.sip_number')->pluck('sip_users.*.sip_number')
        );
    }

    public function test_manager_can_see_users_only_from_his_company(): void
    {
        $manager = $this->loginAsEmployeesManager();
        $this->createUsers($manager->company);
        $newCompany = Company::factory()->create();
        $this->createUsers($newCompany);

        $query = sprintf(
            'query { %s (per_page: %s) { data { first_name email } } }',
            self::QUERY,
            50
        );
        $result = $this->postGraphQL(['query' => $query]);

        $users = $result->json('data.' . self::QUERY . '.data');

        $this->assertDatabaseCount(
            User::TABLE,
            41
        );

        self::assertCount(21, $users);
    }

    public function test_manager_can_see_his_permission_for_users_in_list(): void
    {
        $manager = $this->loginAsEmployeesManager();
        $this->createUsers($manager->company, 2);

        $query = sprintf(
            'query { %s { data { first_name last_name email permission } } }',
            self::QUERY
        );

        $result = $this->postGraphQL(['query' => $query]);

        $users = $result->json('data.' . self::QUERY . '.data');

        self::assertCount(3, $users);

        $usersWithUpdatePermission = array_filter(
            $users,
            static fn($user) => isset($user['permission']) && in_array(
                    BasePermissionField::UPDATE,
                    $user['permission'],
                    true
                )
        );

        $usersWithDeletePermission = array_filter(
            $users,
            static fn($user) => isset($user['permission']) && in_array(
                    BasePermissionField::DELETE,
                    $user['permission'],
                    true
                )
        );

        self::assertCount(3, $usersWithUpdatePermission);
        self::assertCount(2, $usersWithDeletePermission);
    }

    public function test_manager_can_find_employee_by_email_chunk(): void
    {
        $manager = $this->loginAsEmployeesManager();
        $searchEmail = 'fake.email@example.com';
        $user = User::factory()->create(['email' => new Email($searchEmail)]);

        $manager->company->users()->attach($user);

        $this->createUsers($manager->company);

        $searchString = 'fake.email';

        $query = sprintf(
            'query { %s (query: "%s" ) { data { first_name email } } }',
            self::QUERY,
            $searchString
        );

        $result = $this->postGraphQL(['query' => $query]);

        $users = $result->json('data.' . self::QUERY . '.data');
        self::assertCount(1, $users);

        $user = array_shift($users);
        self::assertEquals($searchEmail, $user['email']);
    }

    public function test_manager_can_sort_users_by_full_name(): void
    {
        $manager = $this->loginAsEmployeesManager();
        $manager->first_name = 'First name 1';
        $manager->middle_name = 'Middle name 1';
        $manager->last_name = 'Last name 1';
        $manager->save();

        $employee1 = User::factory()->withCompany($manager->company)->create(
            [
                'first_name' => 'First name 3',
                'middle_name' => 'Middle name 3',
                'last_name' => 'Last name 3',
            ]
        );

        $employee2 = User::factory()->withCompany($manager->company)->create(
            [
                'first_name' => 'First name 2',
                'middle_name' => 'Middle name 2',
                'last_name' => 'Last name 2',
            ]
        );

        $query = sprintf(
            'query { %s (sort: "%s") { data { id first_name last_name middle_name } } }',
            self::QUERY,
            'name-asc'
        );

        $result = $this->postGraphQL(['query' => $query]);

        $users = collect(
            $result->json('data.' . self::QUERY . '.data')
        );

        $user = $users->shift();
        self::assertEquals($manager->id, $user['id']);

        $user = $users->shift();
        self::assertEquals($employee2->id, $user['id']);

        $user = $users->shift();
        self::assertEquals($employee1->id, $user['id']);
    }

    public function test_manager_can_get_employee_by_id(): void
    {
        $manager = $this->loginAsEmployeesManager();
        $users = $this->createUsers($manager->company);
        $user = $users->random();

        $query = sprintf(
            'query { %s ( id: %s ) { data { id first_name email } } }',
            self::QUERY,
            $user->id
        );

        $result = $this->postGraphQL(['query' => $query]);
        $data = $result->json('data.' . self::QUERY . '.data');

        self::assertCount(1, $data);
        self::assertEquals($user->id, $data[0]['id']);
        self::assertEquals($user->first_name, $data[0]['first_name']);
        self::assertEquals($user->email, $data[0]['email']);
    }

    public function test_manager_can_see_employees_roles(): void
    {
        $manager = $this->loginAsEmployeesManager();
        $users = $this->createUsers($manager->company);
        $user = $users->random();
        $role = Role::factory()->create();
        $user->assignRole($role);

        $query = sprintf(
            'query { %s ( id: %s ) { data { id first_name roles { id translate { title } } } } }',
            self::QUERY,
            $user->id
        );

        $result = $this->postGraphQL(['query' => $query]);
        $rolesData = $result->json('data.' . self::QUERY . '.data.0.roles');

        self::assertEquals(
            [
                'id' => $role->id,
                'translate' => [
                    'title' => $role->translate->title
                ]
            ],
            $rolesData[0]
        );
    }
}
