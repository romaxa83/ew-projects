<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('jd:delete-report')->daily();
        $schedule->command('jd:delete-excel')->daily();
        $schedule->command('jd:sync')->daily();

//        $schedule->command('jd:worker-test')->everyMinute();

        $schedule->command('jd:report:push')->dailyAt('10:00');
        $schedule->command('jd:report:push-in-start-day')->weekdays()->at('09:00');
        $schedule->command('jd:report:push-in-end-day')->weekdays()->at('18:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
