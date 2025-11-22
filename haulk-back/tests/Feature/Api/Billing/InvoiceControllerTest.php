<?php


namespace Api\Billing;


use App\Broadcasting\Events\Subscription\SubscriptionUpdateBroadcast;
use App\Console\Commands\Billing\CreateInvoices;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\Helpers\Traits\Billing\BillingMethodsHelper;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
{
    use DatabaseTransactions;
    use BillingMethodsHelper;

    public function test_pay_invoice_manual(): void
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

        Event::fake([SubscriptionUpdateBroadcast::class]);

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

        $this->putJson(route('billing.pay-invoice', $company->invoices->first()))
            ->assertOk();

        Event::assertDispatched(SubscriptionUpdateBroadcast::class, 3);

        $company->refresh();

        $this->assertFalse($company->hasUnpaidInvoices());
    }
}
