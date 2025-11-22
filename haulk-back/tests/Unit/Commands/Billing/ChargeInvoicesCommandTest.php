<?php


namespace Tests\Unit\Commands\Billing;


use App\Console\Commands\Billing\ChargeInvoices;
use App\Console\Commands\Billing\CreateInvoices;
use App\Enums\Saas\GPS\DeviceStatus;
use App\Enums\Saas\GPS\DeviceSubscriptionStatus;
use App\Models\Billing\Invoice;
use App\Models\Saas\GPS\Device;
use App\Notifications\Billing\BlockedAccount;
use App\Services\Permissions\Payments\AuthorizeNetPaymentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Mockery\MockInterface;
use stringEncode\Exception;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Saas\GPS\DeviceSubscriptionsBuilder;
use Tests\Helpers\Traits\Billing\BillingMethodsHelper;
use Tests\TestCase;

class ChargeInvoicesCommandTest extends TestCase
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

    public function test_charge_invoice(): void
    {
        $company = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'));

        $this->assertEquals(0, $company->invoices->count());

        $this->assertSame(config('pricing.plans.regular.slug'), $company->subscription->pricingPlan->slug);
        $this->assertTrue($company->isSubscriptionActive());
        $this->assertFalse($company->isInTrialPeriod());

        $subscription = $company->subscription;
        $billingStart = now()
            ->subDay()
            ->subMonth()
            ->startOfDay();

        $subscription->billing_start = $billingStart;
        $subscription->billing_end = now()
            ->subDay()
            ->endOfDay();
        $subscription->save();

        collect(
            $this->generateDateCount($billingStart)
        )->map(
            function ($el) use ($company) {
                $this->getBillingService()
                    ->createDriverHistoryRecord($company, $el['driver_count'], $el['date']);
            }
        );

        $this->artisan(CreateInvoices::class);

        $company->refresh();

        $this->assertNotEquals(0, $company->invoices->count());
        $this->assertTrue($company->hasUnpaidInvoices());

        $this->loginAsCarrierSuperAdmin($company->getSuperAdmin());

        $this->postJson(
            route('billing.update-payment-method'),
            $this->getRandomPaymentMethodData()
        )
            ->assertOk();

        $this->artisan(ChargeInvoices::class);

        $company->refresh();

        $this->assertFalse($company->hasUnpaidInvoices());
    }

    public function test_error_charge_invoice(): void
    {
        $company = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'));

        $this->assertEquals(0, $company->invoices->count());

        $this->assertSame(config('pricing.plans.regular.slug'), $company->subscription->pricingPlan->slug);
        $this->assertTrue($company->isSubscriptionActive());
        $this->assertFalse($company->isInTrialPeriod());

        $subscription = $company->subscription;
        $billingStart = now()
            ->subDay()
            ->subMonth()
            ->startOfDay();

        $subscription->billing_start = $billingStart;
        $subscription->billing_end = now()
            ->subDay()
            ->endOfDay();
        $subscription->save();

        collect(
            $this->generateDateCount($billingStart)
        )->map(
            function ($el) use ($company) {
                $this->getBillingService()
                    ->createDriverHistoryRecord($company, $el['driver_count'], $el['date']);
            }
        );

        $this->artisan(CreateInvoices::class);

        $company->refresh();

        $this->assertNotEquals(0, $company->invoices->count());
        $this->assertTrue($company->hasUnpaidInvoices());

        /**@var Invoice $invoice*/
        $invoice = $company->invoices->first();

        $invoice->attempt = config('billing.invoices.max_charge_attempts') - 1;
        $invoice->save();

        $this->partialMock(
            AuthorizeNetPaymentService::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('makePayment')->andThrow(Exception::class);
            }
        );

        $this->loginAsCarrierSuperAdmin($company->getSuperAdmin());

        $this->postJson(
            route('billing.update-payment-method'),
            $this->getRandomPaymentMethodData()
        )
            ->assertOk();

        Notification::fake();

        $this->artisan(ChargeInvoices::class);

        Notification::assertSentTo(new AnonymousNotifiable(), BlockedAccount::class);

        $invoice->refresh();

        $this->assertEquals(3, $invoice->attempt);
    }

    /** @test */
    public function error_charge_invoice_has_gps_subscription(): void
    {
        $company = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'));

        $gpsSubscription = $this->deviceSubscriptionsBuilder
            ->status(DeviceSubscriptionStatus::ACTIVE())
            ->company($company)
            ->create();

        /** @var $d_1 Device */
        $d_1 = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::ACTIVE())->create();
        $d_2 = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::ACTIVE())->create();

        $this->assertEquals(0, $company->invoices->count());

        $this->assertSame(config('pricing.plans.regular.slug'), $company->subscription->pricingPlan->slug);
        $this->assertTrue($company->isSubscriptionActive());
        $this->assertFalse($company->isInTrialPeriod());

        $subscription = $company->subscription;
        $billingStart = now()
            ->subDay()
            ->subMonth()
            ->startOfDay();

        $subscription->billing_start = $billingStart;
        $subscription->billing_end = now()
            ->subDay()
            ->endOfDay();
        $subscription->save();

        collect(
            $this->generateDateCount($billingStart)
        )->map(
            function ($el) use ($company) {
                $this->getBillingService()
                    ->createDriverHistoryRecord($company, $el['driver_count'], $el['date']);
            }
        );

        $this->artisan(CreateInvoices::class);

        $company->refresh();

        $this->assertNotEquals(0, $company->invoices->count());
        $this->assertTrue($company->hasUnpaidInvoices());

        /**@var Invoice $invoice*/
        $invoice = $company->invoices->first();

        $invoice->attempt = config('billing.invoices.max_charge_attempts') - 1;
        $invoice->save();

        $this->partialMock(
            AuthorizeNetPaymentService::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('makePayment')->andThrow(Exception::class);
            }
        );

        $this->loginAsCarrierSuperAdmin($company->getSuperAdmin());

        $this->postJson(
            route('billing.update-payment-method'),
            $this->getRandomPaymentMethodData()
        )
            ->assertOk();


        $this->assertTrue($d_1->status->isActive());
        $this->assertNull($d_1->active_till_at);
        $this->assertTrue($d_1->status_activate_request->isNone());
        $this->assertTrue($d_1->status_request->isNone());

        $this->assertTrue($d_2->status->isActive());
        $this->assertNull($d_2->active_till_at);
        $this->assertTrue($d_2->status_activate_request->isNone());
        $this->assertTrue($d_2->status_request->isNone());

        $this->artisan(ChargeInvoices::class);

        $invoice->refresh();
        $d_1->refresh();
        $d_2->refresh();

        $this->assertTrue($d_1->status->isActive());
        $this->assertNotNull($d_1->active_till_at);
        $this->assertTrue($d_1->status_activate_request->isDeactivate());
        $this->assertTrue($d_1->status_request->isPending());

        $this->assertTrue($d_2->status->isActive());
        $this->assertNotNull($d_2->active_till_at);
        $this->assertTrue($d_2->status_activate_request->isDeactivate());
        $this->assertTrue($d_2->status_request->isPending());

        $this->assertEquals(3, $invoice->attempt);
    }
}
