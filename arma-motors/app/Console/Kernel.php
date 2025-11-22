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

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
//        $schedule->command('am:worker:test')->everyMinute();
        $schedule->command('telescope:prune')->daily();
        $schedule->command('am:worker:remove-email-token')->daily();
        $schedule->command('am:worker:remove-sms-token')->daily();
        $schedule->command('am:worker:remove-aa-responses')->daily();
        $schedule->command('am:worker:remind-order')->everyFiveMinutes();
        $schedule->command('am:worker:remove-aa-free-slots')->dailyAt("23:55");
    }
//* * * * * cd /home/wzdev/web/arma-motors.wezom.agency/public_html && php80 artisan schedule:run >> /dev/null 2>&1

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
