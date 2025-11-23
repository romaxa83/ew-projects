<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use WezomCms\Orders\Jobs\CheckSdekOrders;
use WezomCms\Orders\Jobs\ClearOldCarts;

class Kernel extends ConsoleKernel
{

    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('telescope:prune')->daily();
        $schedule->command('cmd:worker:remove-sms-token')->daily();
        $schedule->command('cmd:worker:collection-start')->everyMinute();
        $schedule->command('cmd:worker:collection-finish')->everyMinute();
        $schedule->command('cmd:worker:collection-soon-finish')->everyMinute();
        $schedule->job(new ClearOldCarts())->dailyAt('01:00');
        $schedule->command('sdek:check-orders')->everyFourHours();
//        $schedule->command('cmd:worker:test')->everyMinute();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
