<?php


namespace Api\Billing;


use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class MobileSubscriptionInfoTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_mobile_subscription_info(): void
    {
        $companyData = $this->getNewCompanyData();

        $company = $this->createCompany($companyData, config('pricing.plans.trial.slug'));
        $driver = $this->driverFactory(['carrier_id' => $company->id]);

        $this->assertTrue($company->isSubscriptionActive());

        $this->loginAsCarrierDriver($driver);

        $this->getJson(route('v1.carrier-mobile.subscription-info'))
            ->assertOk()
            ->assertJsonPath('data.subscription_active', $company->isSubscriptionActive());
    }
}
