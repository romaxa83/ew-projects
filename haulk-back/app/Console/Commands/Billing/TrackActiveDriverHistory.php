<?php

namespace App\Console\Commands\Billing;

use App\Models\Saas\Company\Company;
use App\Services\Billing\BillingService;
use Illuminate\Console\Command;

class TrackActiveDriverHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:track-active-drivers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     */
    public function handle(): void
    {
        /** @var $billingService BillingService */
        $billingService = resolve(BillingService::class);

        Company::whereHas(
            'subscription',
            function ($query) {
                $query->where(
                    'canceled',
                    false
                )->where(
                    function ($q) {
                        $q->where(
                            [
                                ['is_trial', true],
                                ['billing_end', '>=', now()->format('Y-m-d H:i:s')],
                            ]
                        )->orWhere('is_trial', false);
                    }
                );
            }
        )->each(
            function ($company) use ($billingService) {
                $billingService->trackCompanyActiveDrivers($company);
            }
        );
    }
}
