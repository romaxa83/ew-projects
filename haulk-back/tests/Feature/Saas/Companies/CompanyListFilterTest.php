<?php

namespace Tests\Feature\Saas\Companies;

use App\Models\Saas\Company\Company;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CompanyListFilterTest extends TestCase
{
    use DatabaseTransactions;

    public function test_order_by_active_companies(): void
    {
        $this->loginAsSaasSuperAdmin();

        Company::factory()->create(['active' => false]);
        Company::factory()->create(['active' => true]);

        $args = [
            'active' => true,
            'per_page' => 1
        ];

        $companies = $this->getJson(route('v1.saas.companies.index', $args))->json('data');

        $company = array_shift($companies);

        static::assertEquals(true, $company['active']);

        $args = [
            'active' => false,
            'per_page' => 1
        ];

        $companies = $this->getJson(route('v1.saas.companies.index', $args))->json('data');

        $company = array_shift($companies);

        static::assertEquals(false, $company['active']);
    }

    public function test_it_orders_companies_by_registered_at_attribute(): void
    {
        $this->loginAsSaasSuperAdmin();

        $originalNow = now();

        Carbon::setTestNow(now()->subDay());

        Company::factory()->times(5)->create();

        Carbon::setTestNow($originalNow);

        Company::factory()->times(5)->create();

        $companies = $this->getJson(route('v1.saas.companies.index'))->json('data');

        self::assertCount(11, $companies); // default company in seeder

        $args = [
            'registered_at' => now()->format('m/d/Y'),
        ];

        $companies = $this->getJson(route('v1.saas.companies.index', $args))->json('data');

        self::assertCount(11, $companies);
    }

    public function test_search_companies(): void
    {
        $this->loginAsSaasSuperAdmin();

        Company::factory()->create(['name' => 'Company name 1', 'usdot' => 123, 'email' => 'email1@mail.com']);
        Company::factory()->create(['name' => 'Company name 2', 'usdot' => 345, 'email' => 'email2@mail.com']);
        Company::factory()->create(['name' => 'Company name 3', 'usdot' => 567, 'email' => 'email3@mail.com']);
        Company::factory()->create(['name' => 'Company name 4', 'usdot' => 789, 'email' => 'email4@mail.com']);
        Company::factory()->create(['name' => 'Company name 5', 'usdot' => 900, 'email' => 'email5@mail.com']);

        $args = [
            'query' => 'Company name 2',
        ];

        $companies = $this->getJson(route('v1.saas.companies.index', $args))->json('data');

        self::assertCount(1, $companies);

        $args = [
            'query' => '900',
        ];

        $companies = $this->getJson(route('v1.saas.companies.index', $args))->json('data');

        self::assertCount(1, $companies);

        $args = [
            'query' => 'email',
        ];

        $companies = $this->getJson(route('v1.saas.companies.index', $args))->json('data');

        self::assertCount(5, $companies);
    }
}
