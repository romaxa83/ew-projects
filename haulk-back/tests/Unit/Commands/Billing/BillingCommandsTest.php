<?php

namespace Tests\Unit\Commands\Billing;

use App\Console\Commands\Billing\CreateInvoices;
use App\Enums\Saas\GPS\DeviceStatus;
use App\Enums\Saas\GPS\DeviceSubscriptionStatus;
use App\Models\Saas\GPS\Device;
use App\Models\Users\User;
use App\Services\Billing\BillingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Saas\GPS\DeviceSubscriptionsBuilder;
use Tests\Helpers\Traits\Billing\BillingMethodsHelper;
use Tests\TestCase;

class BillingCommandsTest extends TestCase
{
    use DatabaseTransactions;
    use BillingMethodsHelper;

    protected DeviceSubscriptionsBuilder $deviceSubscriptionsBuilder;
    protected DeviceBuilder $deviceBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceSubscriptionsBuilder = resolve(DeviceSubscriptionsBuilder::class);
        $this->deviceBuilder = resolve(DeviceBuilder::class);
    }

    private function generateDateCount(Carbon $firstDay): array
    {
        $firstDay->addDays(random_int(3, 10));
        $result = [];

        foreach (range(0, random_int(3, 15)) as $i) {
            $day = $firstDay->addDay();
            $result[] = [
                'date' => $day->format('Y-m-d'),
                'driver_count' => random_int(0, 20),
            ];
        }

        return $result;
    }

    private function getBillingService(): BillingService
    {
        return resolve(BillingService::class);
    }

    public function test_trial_plan()
    {
        $company = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.trial.slug'));

        $this->assertSame(config('pricing.plans.trial.slug'), $company->subscription->pricingPlan->slug);
        $this->assertTrue($company->isSubscriptionActive());
        $this->assertTrue($company->isInTrialPeriod());

        $subscription = $company->subscription;

        // check active trial
        $subscription->billing_start = now()
            ->subDay()
            ->startOfDay();
        $subscription->billing_end = now()
            ->subDay()
            ->addMonth()
            ->endOfDay();
        $subscription->save();

        $superadmin = User::query()
            ->where('carrier_id', $company->id)
            ->first();

        $this->loginAsCarrierSuperAdmin($superadmin);

        // check trial with no card
        $this->getJson(route('orders.index'))
//            ->dump()
            ->assertOk();

        // add card info
        $this->postJson(
            route('billing.update-payment-method'),
            $this->getRandomPaymentMethodData()
        )
            ->assertOk();

        // check trial with card
        $this->getJson(route('orders.index'))
            ->assertOk();
    }

    public function test_trial_to_regular()
    {
        $company = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.trial.slug'));

        $this->assertSame(config('pricing.plans.trial.slug'), $company->subscription->pricingPlan->slug);
        $this->assertTrue($company->isSubscriptionActive());
        $this->assertTrue($company->isInTrialPeriod());

        $subscription = $company->subscription;

        $subscription->billing_start = now()->subDays(5)->startOfDay();
        $subscription->billing_end = now()->subDays(2)->endOfDay();
        $subscription->save();

        // test no regular if no payment method and check one invoice created
        $this->artisan(CreateInvoices::class);

        $company->refresh();

        $this->assertSame(config('pricing.plans.trial.slug'), $company->subscription->pricingPlan->slug);
        $this->assertSame(1, $company->invoices->count());
        $this->assertFalse($company->isSubscriptionActive());
        $this->assertFalse($company->isInTrialPeriod());

        // test regular if payment method present and check one invoice created
        $paymentMethod = $company->paymentMethod()->create([]);
        $paymentMethod->payment_provider = 'test';
        $paymentMethod->payment_data = ['test'];
        $paymentMethod->save();

        $this->artisan(CreateInvoices::class);

        $company->refresh();

        $this->assertSame(config('pricing.plans.regular.slug'), $company->subscription->pricingPlan->slug);
        $this->assertSame(1, $company->invoices->count());
        $this->assertTrue($company->isSubscriptionActive());
        $this->assertFalse($company->isInTrialPeriod());

        // check still one invoice
        $this->artisan(CreateInvoices::class);

        $company->refresh();

        $this->assertSame(1, $company->invoices->count());
    }

    public function test_generate_invoice()
    {
        $company1 = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'));
        $company2 = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'));

        $this->assertEquals(0, $company1->invoices->count());
        $this->assertEquals(0, $company2->invoices->count());

        $this->assertSame(config('pricing.plans.regular.slug'), $company1->subscription->pricingPlan->slug);
        $this->assertTrue($company1->isSubscriptionActive());
        $this->assertFalse($company1->isInTrialPeriod());

        $this->assertSame(config('pricing.plans.regular.slug'), $company2->subscription->pricingPlan->slug);
        $this->assertTrue($company2->isSubscriptionActive());
        $this->assertFalse($company2->isInTrialPeriod());

        // create 1st company fake driver count
        $subscription = $company1->subscription;
        $billingStart = now()
            ->subDay()
            ->subMonth()
            ->startOfDay();

        $subscription->billing_start = $billingStart;
        $subscription->billing_end = now()
            ->subDay()
            ->endOfDay();
        $subscription->save();

        $gpsSubscription = $this->deviceSubscriptionsBuilder
            ->status(DeviceSubscriptionStatus::ACTIVE())
            ->company($company1)
            ->create();

        $d_1 = $this->deviceBuilder->company($company1)
            ->status(DeviceStatus::ACTIVE())->create();
        $d_2 = $this->deviceBuilder->company($company1)
            ->status(DeviceStatus::ACTIVE())->create();
        $d_3 = $this->deviceBuilder->company($company1)
            ->status(DeviceStatus::ACTIVE())->create();
        $d_4 = $this->deviceBuilder->company($company1)
            ->status(DeviceStatus::ACTIVE())->create();

        collect(
            $this->generateDateCount($billingStart)
        )->map(
            function ($el) use ($company1) {
                $this->getBillingService()
                    ->createDriverHistoryRecord($company1, $el['driver_count'], $el['date']);
            }
        );

        // create 2nd company fake driver count
        collect(
            $this->generateDateCount(now())
        )->map(
            function ($el) use ($company2) {
                $this->getBillingService()
                    ->createDriverHistoryRecord($company2, $el['driver_count'], $el['date']);
            }
        );

        // run command
        $this->artisan(CreateInvoices::class);

        $company1->refresh();

        $this->assertNotEquals(0, $company1->invoices->count());
        $this->assertEquals(0, $company2->invoices->count());
    }
}
