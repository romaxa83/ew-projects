<?php

namespace App\Console;

use App\Console\Commands\Customers\CustomerTaxExemptionCommand;
use App\Console\Commands\Orders\OrderDeliveryStatusUpdate;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        $schedule->command('telescope:prune', ['--hours' => 4])->hourly();
        $schedule->command('telescope:clear')->daily();

        $schedule->command(OrderDeliveryStatusUpdate::class)->hourly();
        $schedule->command(CustomerTaxExemptionCommand::class)->daily();

        $schedule->command(Workers\Remove\Orders\Parts\OrderDraft::class)->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load([
            __DIR__.'/Commands',
            __DIR__ . '/Workers',
        ]);

        require base_path('routes/console.php');
    }
}
