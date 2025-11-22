<?php


namespace Commands\Billing;


use App\Console\Commands\Billing\ChargeInvoices;
use App\Console\Commands\Billing\CreateInvoices;
use App\Console\Commands\Billing\UpdatePendingTransactionStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\Billing\BillingMethodsHelper;
use Tests\TestCase;

class UpdateTransactionStatusTest extends TestCase
{
    use DatabaseTransactions;
    use BillingMethodsHelper;

    public function test_pending_invoice(): void
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

        $invoice = $company->invoices->first();

        $invoice->is_paid = false;
        $invoice->paid_at = null;
        $invoice->pending = true;
        $invoice->save();

        $this->artisan(UpdatePendingTransactionStatus::class);

        $invoice->refresh();

        $this->assertTrue($invoice->is_paid);
        $this->assertFalse($invoice->pending);
    }
}
