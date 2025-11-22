<?php

namespace App\Console\Commands\Billing;

use App\Models\Saas\Company\Company;
use App\Services\Billing\BillingService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class CancelSubscriptionAfterMonthUnpaid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:cancel-after-month';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel subscription after month since last failed payment attempt';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        /** @var $billingService BillingService */
        $billingService = resolve(BillingService::class);

        $timestamp = now()
            ->subDays(config('billing.failed_payment_cancel_subscription_after_days'))
            ->timestamp;

        Company::whereHas(
            'invoices',
            function (Builder $q) use ($timestamp) {
                $q->where(
                    [
                        ['is_paid', false],
                        ['attempt', '>=', config('billing.invoices.max_charge_attempts')],
                        ['last_attempt_time', '<', $timestamp]
                    ]
                );
            }
        )->get(
        )->each(
            function (Company $company) use ($billingService) {
                $company->subscription->canceled = true;
                $company->subscription->save();

                $billingService->clearCompanyDriverHistory($company->id);

                $company->deactivate();
            }
        );

        return 0;
    }
}
