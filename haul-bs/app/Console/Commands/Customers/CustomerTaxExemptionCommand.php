<?php

namespace App\Console\Commands\Customers;

use App\Enums\Customers\CustomerTaxExemptionStatus;
use App\Models\Customers\CustomerTaxExemption;
use Illuminate\Console\Command;
use Throwable;
class CustomerTaxExemptionCommand extends Command
{
    protected $signature = 'customers:tax-expired';

    protected $description = 'Просрочка tax exemption';

    /**
     * @throws Throwable
     */
    public function handle(): int
    {
        $taxExemptions = CustomerTaxExemption::query()
            ->where('status', CustomerTaxExemptionStatus::ACCEPTED)
            ->where('date_active_to', '<=', now())
            ->get();

        $taxExemptions->each(function (CustomerTaxExemption $exemption) {
            $exemption->update(['status' => CustomerTaxExemptionStatus::EXPIRED]);
        });

        return self::SUCCESS;
    }
}
