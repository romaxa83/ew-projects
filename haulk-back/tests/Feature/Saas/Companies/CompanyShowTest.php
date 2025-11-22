<?php

namespace Tests\Feature\Saas\Companies;

use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\DeviceSubscription;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Saas\GPS\DeviceSubscriptionsBuilder;
use Tests\TestCase;

class CompanyShowTest extends TestCase
{
    use DatabaseTransactions;

    protected DeviceBuilder $deviceBuilder;
    protected DeviceSubscriptionsBuilder $deviceSubscriptionsBuilder;
    protected CompanyBuilder $companyBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceSubscriptionsBuilder = resolve(DeviceSubscriptionsBuilder::class);
        $this->deviceBuilder = resolve(DeviceBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    public function test_cat_view_company_info(): void
    {
        $this->loginAsSaasSuperAdmin();

        $company = Company::factory()->create()->toArray();

        $companyInfo = $this->getJson(route('v1.saas.companies.show', $company['id']))->json('data');

        self::assertEquals($company['name'], $companyInfo['name']);
        self::assertEquals($company['usdot'], $companyInfo['usdot']);
        self::assertEquals($company['email'], $companyInfo['email']);
    }

    /** @test */
    public function success_show(): void
    {
        $this->loginAsSaasSuperAdmin();

        /** @var $company Company */
        $company = $this->companyBuilder
            ->trial($this->companyBuilder->create());

        /** @var $deviceSubscription DeviceSubscription */
        $deviceSubscription = $this->deviceSubscriptionsBuilder->company($company)->create();

        $this->getJson(route('v1.saas.companies.show', $company['id']))
            ->assertJson([
                'data' => [
                    'id' => $company->id,
                    'gps_subscription' => [
                        'id' => $deviceSubscription->id,
                        'status' => $deviceSubscription->status,
                        'start_at' => null,
                        'end_at' => null,
                        'active_till_at' => null,
                        'access_till_at' => null,
                        'total_device' => 0,
                        'total_active' => 0,
                        'total_inactive_device' => 0,
                        'has_active_at_vehicle' => false,
                        'current_rate' => config('billing.gps.price'),
                        'next_rate' => null,
                    ]
                ]
            ])
        ;

    }
}
