<?php

namespace Tests\Feature\Queries\BackOffice\Companies;

use App\GraphQL\Queries\BackOffice\Companies\CompaniesQueryForAdminPanel;
use App\Models\Admins\Admin;
use App\Models\Companies\Company;
use App\Models\Companies\CompanyUser;
use App\Models\Users\User;
use App\Permissions\Companies\CompanyAdminListPermission;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

class CompaniesQueryTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperHelperTrait;

    public const QUERY = CompaniesQueryForAdminPanel::NAME;
    public const COUNT = 3;

    public function test_cant_get_list_of_companies_for_simple_user(): void
    {
        $this->loginAsUser();

        $this->test_cant_get_list_of_companies();
    }

    public function test_cant_get_list_of_companies(): void
    {
        $query = sprintf(
            'query { %s { data { name } } }',
            self::QUERY
        );
        $result = $this->postGraphQLBackOffice(['query' => $query])
            ->assertOk();
        $this->assertGraphQlUnauthorized($result);
    }

    public function test_cant_get_list_of_companies_for_not_permitted_admin(): void
    {
        $this->loginAsAdmin();

        $this->test_cant_get_list_of_companies();
    }

    public function test_admin_with_correct_permission_can_see_a_paginated_list_of_companies(): void
    {
        $this->loginAsCompaniesAdmin();
        $this->createCompanies();

        $query = sprintf(
            'query { %s (per_page: %s) { data { id name language { name } } } }',
            self::QUERY,
            self::COUNT
        );
        $result = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk();
        $data = $result->json('data.' . self::QUERY . '.data');

        self::assertCount(self::COUNT, $data);
    }

    protected function loginAsCompaniesAdmin(): Admin
    {
        return $this
            ->loginAsAdmin()
            ->assignRole(
                $this->generateRole(
                    'CompaniesAdmin',
                    [
                        CompanyAdminListPermission::KEY
                    ],
                    Admin::GUARD
                )
            );
    }

    protected function createCompanies(int $number = self::COUNT): Collection
    {
        return Company::factory($number)->create(['lang' => 'uk']);
    }

    public function test_sort(): void
    {
        Http::fake();

        $this->loginAsCompaniesAdmin();

        $companies = $this->createCompanies();

        foreach ($companies as $company) {
            User::factory()->withCompany($company, isOwner: true)->create();
        }

        $this->sortCompaniesByEmail();
        $this->sortCompaniesByBalance();
        $this->sortCompaniesByCreditLimit();
        $this->sortCompaniesByCreatedAt();
        $this->sortCompaniesByStatus();
    }

    public function sortCompaniesByEmail(): void
    {
        $query = sprintf(
            'query { %s (sort: "%s", per_page: %d) { data { id name, email } } }',
            self::QUERY,
            'email-desc',
            self::COUNT
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])->assertOk();
        $data = collect($result->json('data.' . self::QUERY . '.data'));

        $this->assertEquals($data->pluck('email'), $data->sortByDesc('email')->pluck('email'));
    }

    public function sortCompaniesByBalance(): void
    {
        $query = sprintf(
            'query { %s (sort: "%s") { data { id name, balance } } }',
            self::QUERY,
            'balance-desc'
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])->assertOk();
        $data = collect($result->json('data.' . self::QUERY . '.data'));

        $this->assertEquals($data->pluck('balance'), $data->sortByDesc('balance')->pluck('balance'));
    }

    public function sortCompaniesByCreditLimit(): void
    {
        $query = sprintf(
            'query { %s (sort: "%s") { data { id name, credit_limit } } }',
            self::QUERY,
            'credit_limit-desc'
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])->assertOk();
        $data = collect($result->json('data.' . self::QUERY . '.data'));

        $this->assertEquals($data->pluck('credit_limit'), $data->sortByDesc('credit_limit')->pluck('credit_limit'));
    }

    public function sortCompaniesByCreatedAt(): void
    {
        $query = sprintf(
            'query { %s (sort: "%s") { data { id name, created_at } } }',
            self::QUERY,
            'created_at-desc'
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])->assertOk();
        $data = collect($result->json('data.' . self::QUERY . '.data'));

        $this->assertEquals($data->pluck('created_at'), $data->sortByDesc('created_at')->pluck('created_at'));
    }

    public function sortCompaniesByStatus(): void
    {
        $query = sprintf(
            'query { %s (sort: "%s") { data { id name, status } } }',
            self::QUERY,
            'status-desc'
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])->assertOk();
        $data = collect($result->json('data.' . self::QUERY . '.data'));

        $this->assertEquals($data->pluck('status'), $data->sortByDesc('status')->pluck('status'));
    }

    public function sortCompaniesByName(): void
    {
        $query = sprintf(
            'query { %s (sort: "%s") { data { id name } } }',
            self::QUERY,
            'name-desc'
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])->assertOk();
        $data = collect($result->json('data.' . self::QUERY . '.data'));

        $this->assertEquals($data->pluck('name'), $data->sortByDesc('name')->pluck('name'));

        Company::query()->update(['name' => null]);

        $query = sprintf(
            'query { %s (sort: "%s") { data { id name } } }',
            self::QUERY,
            'name-desc'
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])->assertOk();
        $data = collect($result->json('data.' . self::QUERY . '.data'));

        $this->assertEquals($data->pluck('name'), $data->sortByDesc('name')->pluck('name'));
    }

    /**
     * @throws Exception
     */
    public function test_admin_can_sort_companies_by_users(): void
    {
        $this->loginAsCompaniesAdmin();

        Http::fake();

        $companies = Company::factory()->count(self::COUNT)->create();

        foreach ($companies as $company) {
            CompanyUser::factory()->count(random_int(1, self::COUNT))->create(['company_id' => $company->id]);
        }

        $query = sprintf(
            'query { %s (sort: "%s") { data { id name, users { first_name }} } }',
            self::QUERY,
            'users-desc'
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])->assertOk();
        $data = collect($result->json('data.' . self::QUERY . '.data'));

        $countLeft = $data->map(static function ($v) {
            $v['users'] = count($v['users']);

            return $v;
        });

        $countRight = $data->map(static function ($v) {
            $v['users'] = count($v['users']);

            return $v;
        })->sortByDesc('users');

        $this->assertEquals($countLeft, $countRight);
    }

    public function test_admin_can_get_company_by_id(): void
    {
        $this->loginAsCompaniesAdmin();
        $companies = $this->createCompanies();
        $company = $companies->random();

        $query = sprintf(
            'query { %s ( id: %s ) { data { id name } } }',
            self::QUERY,
            $company->id
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);
        $data = $result->json('data.' . self::QUERY . '.data');

        self::assertCount(1, $data);
        $companyData = array_shift($data);
        self::assertEquals($company->id, $companyData['id']);
        self::assertEquals($company->name, $companyData['name']);
    }

    public function test_admin_can_get_filter_by_user_name(): void
    {
        $this->loginAsCompaniesAdmin();

        $lastName = 'test';
        $email = 'test@test.test';

        $companyUser = CompanyUser::factory()->count(self::COUNT)->create();

        CompanyUser::query()->where('company_id', $companyUser->first()->company_id)
            ->where('user_id', $companyUser->first()->user_id)
            ->update(['state' => Company::STATE_OWNER]);

        User::query()->update(['last_name' => $lastName]);
        Company::query()->update(['name' => '']);

        $query = sprintf(
            'query { %s (name: "%s" ) { data { id name } } }',
            self::QUERY,
            $lastName
        );


        $result = $this->postGraphQLBackOffice(['query' => $query]);
        $data = $result->json('data.' . self::QUERY . '.data');

        self::assertCount(1, $data);

        User::query()->first()->update(['email' => $email]);

        $query = sprintf(
            'query { %s (name: "%s" ) { data { id name, email } } }',
            self::QUERY,
            $email
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);
        $data = $result->json('data.' . self::QUERY . '.data');

        self::assertCount(1, $data);
    }
}
