<?php

namespace App\Console\Commands;

use App\Models\Payrolls\Payroll;
use Illuminate\Console\Command;

class PurgePaidPayrolls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payrolls:purge-paid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge paid payrolls after 6 months';

    /**
     * Execute the console command.
     *
     */
    public function handle(): void
    {
        $this->info('Starting..');

        $timestamp = now()
            ->subMonths(config('payrolls.delete_after_months'))
            ->timestamp;

        Payroll::withoutGlobalScopes(
        )->where(
            [
                ['is_paid', true],
                ['paid_at', '<', $timestamp],
            ]
        )->delete();

        $this->info('Finished..');
    }
}
