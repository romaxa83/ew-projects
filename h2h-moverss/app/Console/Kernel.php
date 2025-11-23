<?php

namespace App\Console;

use App\Console\Commands\ERP\ImportSiteOrdersCommand;
use App\Console\Commands\Import\AuthorizePaymentsCommand;
use App\Console\Commands\Import\GmailCommand;
use App\Console\Commands\System\UpdateRouteListCommand;
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
        ImportSiteOrdersCommand::class,
        AuthorizePaymentsCommand::class,
        GmailCommand::class,
        UpdateRouteListCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('cache:clear')->weeklyOn(6, '3:00')
            ->description('Laravel Clean cache');

        $schedule->command('system:update-route-list')->weeklyOn(6, '4:00')
            ->description('Update route list');

        $schedule->command('site:import-orders la')
            ->everyFiveMinutes()
            ->description('Load new orders from LA site');

        $schedule->command('site:import-authorize-payments')
            ->everyTenMinutes()
            ->description('Load new payments from Authorize');

        $schedule->command('site:import-gmail cron')
            ->everyThirtyMinutes()
            ->description('Import mail from Gmail')
            ->withoutOverlapping();

        $schedule->command('site:import-orders h2h')
            ->everyFiveMinutes()
            ->description('Load new orders from H2H site (Chicago)');

        $schedule->command('worker:remove-incoming-call')
            ->everyFiveMinutes()
            ->description('Remove old incoming call');

//        $schedule->command('ringostat:sync-employees')
//            ->everySixHours();
    }

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
