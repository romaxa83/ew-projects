<?php

namespace Commands\Billing;

use App\Console\Commands\Billing\CancelSubscriptionAfterMonthUnpaid;
use App\Console\Commands\Billing\CreateInvoices;
use App\Services\Billing\BillingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CancelSubscriptionAfterMonthUnpaidTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cancel_company_after_month_unpaid(): void
    {
        $companyData = $this->getNewCompanyData();

        $company = $this->createCompany($companyData, config('pricing.plans.regular.slug'), true);

        $this->assertTrue($company->isSubscriptionActive());

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
            $this->generateDateCount($billingStart, now())
        )->map(
            function ($el) use ($company) {
                $this->getBillingService()
                    ->createDriverHistoryRecord($company, $el['driver_count'], $el['date']);
            }
        );

        $this->artisan(CreateInvoices::class);

        $company->refresh();

        $this->assertTrue($company->hasUnpaidInvoices());

        $invoice = $company->invoices->first();

        $invoice->attempt = 3;
        $invoice->last_attempt_time = now()->subMonths(2)->timestamp;
        $invoice->save();

        $this->artisan(CancelSubscriptionAfterMonthUnpaid::class);

        $this->postJson(
            route('auth.login'),
            [
                'email' => $companyData['email'],
                'password' => $companyData['password'],
            ]
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function generateDateCount(Carbon $start, Carbon $end): array
    {
        $period = $start->toPeriod($end);
        $result = [];

        foreach ($period as $day) {
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
}
