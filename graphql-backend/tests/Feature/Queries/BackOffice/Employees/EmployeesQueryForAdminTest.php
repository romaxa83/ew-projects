<?php

namespace Tests\Feature\Queries\BackOffice\Employees;

use App\GraphQL\Queries\BackOffice\Employees\EmployeesQueryForAdmin;
use App\Models\Admins\Admin;
use App\Models\Companies\Company;
use App\Models\Companies\CompanyUser;
use App\Models\Users\User;
use App\Permissions\Employees\EmployeeListPermission;
use App\ValueObjects\Email;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

class EmployeesQueryForAdminTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperHelperTrait;

    public const QUERY = EmployeesQueryForAdmin::NAME;

    protected ?Collection $users;
    protected ?Company $company1;
    protected ?Company $company2;

    public function tearDown(): void
    {
        parent::tearDown();
        $this->users = null;
        $this->company1 = null;
        $this->company2 = null;
    }

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
        $result = $this->postGraphQLBackOffice(['query' => $query]);
        $this->assertGraphQlUnauthorized($result);
    }

    public function test_cant_get_list_of_employees_for_not_permitted_admin(): void
    {
        $this->loginAsAdmin();

        $this->test_cant_get_list_of_employees();
    }

    public function test_admin_with_correct_permission_can_see_a_list_of_employees(): void
    {
        $this->loginAsEmployeesAdmin();
        $this->createUsers();

        $query = sprintf(
            'query { %s (per_page: %s) { data { first_name email } } }',
            self::QUERY,
            50
        );
        $result = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk();

        $users = $result->json('data.' . self::QUERY . '.data');

        self::assertCount(20, $users);

        $query = sprintf(
            'query { %s (per_page: %s) { data { first_name email } } }',
            self::QUERY,
            10
        );
        $result = $this->postGraphQLBackOffice(['query' => $query]);

        $users = $result->json('data.' . self::QUERY . '.data');

        self::assertCount(10, $users);
    }

    protected function loginAsEmployeesAdmin(): Admin
    {
        return $this
            ->loginAsAdmin()
            ->assignRole(
                $this->generateRole(
                    'EmployeeManager',
                    [
                        EmployeeListPermission::KEY
                    ],
                    Admin::GUARD
                )
            );
    }

    protected function createUsers(): void
    {
        $this->users = User::factory()->times(20)->create();
        $this->company1 = Company::factory()->create();
        $this->company2 = Company::factory()->create();

        $this->company1->users()->attach($this->users->take(8));
        $this->company2->users()->attach($this->users->take(-12));

        CompanyUser::query()->upsert(
            [
                'user_id' => $this->users->first()->id,
                'company_id' => $this->company1->id,
                'state' => Company::STATE_OWNER
            ],
            [
                'user_id',
                'company_id'
            ]
        );

        CompanyUser::query()->upsert(
            [
                'user_id' => $this->users->last()->id,
                'company_id' => $this->company2->id,
                'state' => Company::STATE_OWNER
            ],
            [
                'user_id',
                'company_id'
            ]
        );
    }

    public function test_admin_can_find_employee_by_email_chunk(): void
    {
        $this->loginAsEmployeesAdmin();
        $this->createUsers();
        $searchEmail = 'fake.email@example.com';
        $user = User::factory()->create(['email' => new Email($searchEmail)]);

        $this->company1->users()->attach($user);

        $searchString = 'fake.email';

        $query = sprintf(
            'query { %s (query: "%s" ) { data { first_name email } } }',
            self::QUERY,
            $searchString
        );

        $result = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk();

        $users = $result->json('data.' . self::QUERY . '.data');
        self::assertCount(1, $users);

        $foundUser = array_shift($users);
        self::assertEquals($searchEmail, $foundUser['email']);
    }

    public function test_can_sort_employee_by_company(): void
    {
        $this->loginAsEmployeesAdmin();
        $this->createUsers();
        $user = User::factory()->create(['email' => new Email('test@ukr.net')]);
        $this->company1->users()->attach($user);

        $this->company1->update(['name' => 'company A']);
        $this->company2->update(['name' => 'company B']);

        $expectedResultBySorting = [
            'company A',
            'company A',
            'company A',
            'company A',
            'company A',
            'company A',
            'company A',
            'company A',
            'company A',
            'company B',
            'company B',
            'company B',
            'company B',
            'company B',
            'company B',
        ];
        $this->checkSort('company', $expectedResultBySorting);

        $expectedResultBySorting = [
            'company B',
            'company B',
            'company B',
            'company B',
            'company B',
            'company B',
            'company B',
            'company B',
            'company B',
            'company B',
            'company B',
            'company B',
            'company A',
            'company A',
            'company A',
        ];
        $this->checkSort('company', $expectedResultBySorting, 'desc');
    }

    protected function checkSort(string $field, array $expectedResult, string $direction = 'asc'): void
    {
        $query = sprintf(
            'query { %s (sort: "%s-%s" ) { data { first_name email company { name } } } }',
            self::QUERY,
            $field,
            $direction
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);

        $data = $result->json('data.' . static::QUERY . '.data');
        foreach ($expectedResult as $item) {
            $this->assertEquals($item, array_shift($data)['company']['name']);
        }
    }

    public function test_admin_can_filter_employees_by_company(): void
    {
        $this->loginAsEmployeesAdmin();

        $this->createUsers();

        $query = sprintf(
            'query { %s (company: %s ) { data { first_name email } } }',
            self::QUERY,
            $this->company1->id
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);

        $users = $result->json('data.' . self::QUERY . '.data');
        self::assertCount(8, $users);

        $query = sprintf(
            'query { %s (company: %s ) { data { first_name email } } }',
            self::QUERY,
            $this->company2->id
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);

        $users = $result->json('data.' . self::QUERY . '.data');
        self::assertCount(12, $users);
    }

    public function test_admin_can_sort_users_by_full_name(): void
    {
        $this->loginAsEmployeesAdmin();

        $this->createUsers();

        $query = sprintf(
            'query { %s (sort: "%s" per_page: 15) { data { first_name last_name middle_name } } }',
            self::QUERY,
            'name-asc'
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);

        $users = $result->json('data.' . self::QUERY . '.data');

        $usersCollection = $this->users
            ->sortBy(
                [
                    ['last_name', 'asc'],
                    ['first_name', 'asc'],
                    ['middle_name', 'asc']
                ]
            )
            ->take(15)
            ->map(
                function ($item) {
                    return [
                        'last_name' => $item['last_name'],
                        'first_name' => $item['first_name'],
                        'middle_name' => $item['middle_name']
                    ];
                }
            )
            ->values()
            ->toArray();

        self::assertEquals($users, $usersCollection);

        $query = sprintf(
            'query { %s (sort: "%s") { data { first_name last_name middle_name } } }',
            self::QUERY,
            'name-desc'
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);

        $users = $result->json('data.' . self::QUERY . '.data');

        $usersCollection = $this->users
            ->sortBy(
                [
                    ['last_name', 'desc'],
                    ['first_name', 'desc'],
                    ['middle_name', 'desc']
                ]
            )
            ->take(15)
            ->map(
                function ($item) {
                    return [
                        'last_name' => $item['last_name'],
                        'first_name' => $item['first_name'],
                        'middle_name' => $item['middle_name']
                    ];
                }
            )
            ->values()
            ->toArray();
        self::assertEquals($users, $usersCollection);

        $query = sprintf(
            'query { %s (page: 2 sort: "%s") { data { first_name last_name middle_name } } }',
            self::QUERY,
            'name-desc'
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);

        $users = $result->json('data.' . self::QUERY . '.data');

        $usersCollection = $this->users
            ->sortBy(
                [
                    ['last_name', 'desc'],
                    ['first_name', 'desc'],
                    ['middle_name', 'desc']
                ]
            )
            ->splice(15, 15)
            ->map(
                function ($item) {
                    return [
                        'last_name' => $item['last_name'],
                        'first_name' => $item['first_name'],
                        'middle_name' => $item['middle_name']
                    ];
                }
            )
            ->values()
            ->toArray();

        self::assertEquals($users, $usersCollection);
    }

    public function test_admin_can_sort_users_by_email_and_paginate_them(): void
    {
        $this->loginAsEmployeesAdmin();

        $this->createUsers();

        $query = sprintf(
            'query { %s (per_page: 5, page: 3, sort: "%s") { data { first_name email } } }',
            self::QUERY,
            'email-asc'
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);

        $users = $result->json('data.' . self::QUERY . '.data');

        $usersCollection = $this->users
            ->sortBy('email')
            ->splice(10, 5)
            ->map(
                function ($item) {
                    return [
                        'first_name' => $item['first_name'],
                        'email' => (string)$item['email']
                    ];
                }
            )
            ->toArray();

        self::assertEquals($users, $usersCollection);
    }

    public function test_admin_can_filter_owners_from_employees_list(): void
    {
        $this->loginAsEmployeesAdmin();

        $this->createUsers();

        $query = sprintf(
            'query { %s ( state: "%s" ) { data { id first_name email company { id name } } } }',
            self::QUERY,
            Company::STATE_OWNER
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);

        $users = $result->json('data.' . self::QUERY . '.data');

        self::assertCount(2, $users);
    }

    public function test_admin_can_get_employee_by_id(): void
    {
        $this->loginAsEmployeesAdmin();
        $this->createUsers();
        $user = $this->users->random();

        $query = sprintf(
            'query { %s ( id: %s ) { data { id first_name email } } }',
            self::QUERY,
            $user->id
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);
        $data = $result->json('data.' . self::QUERY . '.data');

        self::assertCount(1, $data);
        $userData = array_shift($data);
        self::assertEquals($user->id, $userData['id']);
        self::assertEquals($user->first_name, $userData['first_name']);
        self::assertEquals($user->email, $userData['email']);
    }

    public function test_sort(): void
    {
        $this->loginAsEmployeesAdmin();
        CompanyUser::factory()->count(3)->create();

        $this->sortCompany();
        $this->sortCreatedAt();
    }

    public function sortCompany(): void
    {
        $query = sprintf(
            'query { %s (sort: "%s") { data { id company {name} } } }',
            self::QUERY,
            'company-desc'
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])->assertOk();
        $data = collect($result->json('data.' . self::QUERY . '.data'));

        $this->assertEquals(
            $data->pluck('company.name'),
            $data->sortByDesc('company.name')->pluck('company.name')
        );
    }

    public function sortCreatedAt(): void
    {
        foreach (CompanyUser::query()->get() as $user) {
            User::query()->where('id', $user->user_id)->update(['created_at' => now()->addDays(random_int(1, 365))]);
        }

        $query = sprintf(
            'query { %s (sort: "%s") { data { id created_at } } }',
            self::QUERY,
            'created_at-desc'
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])->assertOk();
        $data = collect($result->json('data.' . self::QUERY . '.data'));

        $this->assertEquals(
            $data->pluck('created_at'),
            $data->sortByDesc('created_at')->pluck('created_at')
        );
    }
}
