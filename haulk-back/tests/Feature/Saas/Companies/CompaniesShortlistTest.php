<?php

namespace Tests\Feature\Saas\Companies;

use App\Enums\Saas\GPS\DeviceSubscriptionStatus;
use App\Models\Saas\Company\Company;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceSubscriptionsBuilder;
use Tests\TestCase;

class CompaniesShortlistTest extends TestCase
{
    use DatabaseTransactions;

    protected CompanyBuilder $companyBuilder;
    protected DeviceSubscriptionsBuilder $deviceSubscriptionsBuilder;
    protected function setUp(): void
    {
        parent::setUp();

        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->deviceSubscriptionsBuilder = resolve(DeviceSubscriptionsBuilder::class);
    }

    public function test_not_permitted_admin_can_not_view_companies(): void
    {
        $this->loginAsSaasAdmin();

        $this->getJson(route('v1.saas.companies.shortlist', ['query' => '123']))
            ->assertForbidden();
    }

    public function test_permitted_admin_can_view_companies(): void
    {
        $this->loginAsSaasSuperAdmin();

        $this->getJson(route('v1.saas.companies.shortlist', ['query' => '123']))->assertOk();
    }

    public function test_shortlist_filter(): void
    {
        $this->loginAsSaasSuperAdmin();

        Company::factory(['name' => 'Test1'])->create();
        $c_1 = Company::factory(['name' => 'Test2'])->create();
        $c_2 = Company::factory(['name' => 'Test3'])->create();
        Company::factory(['name' => 'New'])->create();
        Company::factory(['name' => 'New 2'])->create();

        $this->deviceSubscriptionsBuilder->company($c_1)
            ->status(DeviceSubscriptionStatus::DRAFT())->create();
        $this->deviceSubscriptionsBuilder->company($c_2)
            ->status(DeviceSubscriptionStatus::ACTIVE())->create();

        $this->getJson(route('v1.saas.companies.shortlist', [
            'query' => 'Test',
            'gps_enabled' => true
        ]))
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function filter_by_id(): void
    {
        $this->loginAsSaasSuperAdmin();

        /** @var $model Company */
        $model = $this->companyBuilder->create();

        $this->companyBuilder->create();
        $this->companyBuilder->create();
        $this->companyBuilder->create();

        $this->getJson(route('v1.saas.companies.shortlist', [
            'id' => $model->id
        ]))
            ->assertOk()
            ->assertJson([
                'data' => [
                    ['id' => $model->id]
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;
    }
}
