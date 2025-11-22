<?php

namespace App\Console\Commands\Billing;

use App\Models\Billing\Invoice;
use App\Models\Saas\Company\Company;
use App\Models\Saas\Pricing\CompanySubscription;
use App\Notifications\Billing\TrialEnd;
use App\Notifications\Billing\TrialEndWithoutPaymentData;
use App\Services\Billing\BillingService;
use App\Services\Events\EventService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CreateInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:create-invoices';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        /** @var $billingService  BillingService*/
        $billingService = resolve(BillingService::class);

        CompanySubscription::query()
            ->whereHas('company', fn($b) => $b->where('active', true))
            ->where([
                ['canceled', false],
                ['billing_end', '<', now()->format('Y-m-d H:i:s')],
            ])
            ->whereNotNull('billing_end')
            ->get()
            ->map(
            function (CompanySubscription $subscription) use ($billingService) {
                $invoiceExist = Invoice::where(
                    [
                        ['carrier_id', $subscription->company_id],
                        ['billing_start', $subscription->billing_start->format('Y-m-d')],
                        ['billing_end', $subscription->billing_end->format('Y-m-d')],
                    ]
                )->exists();

                /** @var $company Company */
                $company = $subscription->company;

                if (!$invoiceExist) {
//
//                    logger_info('CREATE INVOICE', [
//                        'company_id' => $company->id,
//                        'company_name' => $company->name,
//                        'billing_start' => $subscription->billing_start->format('Y-m-d'),
//                        'billing_end' => $subscription->billing_end->format('Y-m-d'),
//                        'now' => CarbonImmutable::now()->toDateTimeString(),
//                    ]);

                    // создание инвойса
                    $invoice = $billingService->createMonthlyInvoice($company);

                    if ($invoice) {
                        $billingService->sendPdfInvoice($invoice);

                        if ($subscription->isTrialExpired() && !$company->hasPaymentMethod()) {
                            $this->sendNotification($company);
                        }
                    }
                }

                if ($subscription->isTrial()) {
                    if ($subscription->isTrialExpired() && $company->hasPaymentMethod()) {
                        $subscription->delete();
                        $company->createSubscription(config('pricing.plans.regular.slug'));

                        $billingService->trackCompanyActiveDrivers($company);

                        $this->sendNotification($company);
                    }
                } else {
                    $company->renewSubscription();
                }

                EventService::billing($company)
                    ->update()
                    ->broadcast();
            }
        );
    }

    /**
     * @param Company $company
     */
    private function sendNotification(Company $company): void
    {
        if ($company->hasPaymentMethod()) {
            Notification::route('mail', $company->getPaymentContactData()['email'])->notify(new TrialEnd($company));
        } else {
            Notification::route('mail', $company->getPaymentContactData()['email'])->notify(new TrialEndWithoutPaymentData($company));
        }
    }
}
