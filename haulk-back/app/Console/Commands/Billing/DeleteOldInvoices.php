<?php

namespace App\Console\Commands\Billing;

use App\Models\Billing\Invoice;
use Illuminate\Console\Command;

class DeleteOldInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:purge-invoices';

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
    public function handle()
    {
        $timestamp = now()
            ->subDays(config('billing.invoices.purge_after_days'))
            ->format('Y-m-d H:i:s');

        Invoice::where(
            'created_at',
            '<',
            $timestamp
        )->whereDoesntHave(
            'company'
        )->delete(
        );
    }
}
