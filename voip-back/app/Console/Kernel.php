<?php

namespace App\Console;

use App\Console\Commands\Admins\CreateAdminCommand;
use App\Console\Commands\Workers\HoldMusic;
use App\Console\Commands\Workers\UnholdMusic;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        CreateAdminCommand::class,
    ];
//0 * * * *
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('telescope:prune')->daily();
        $schedule->command('auth:remove-expired-tokens')->daily();
        $schedule->command('workers:clear_log_file')->daily();
        $schedule->command('workers:sync_queue_log')->daily();
        $schedule->command('workers:remove_old_queue_recs')->hourly();
        $schedule->command('workers:remove_old_excel')->daily();
        $schedule->command('workers:restart_pami_listener')->hourly();
        $schedule->command('workers:exist_sip_to_location')->everyMinute();
        $schedule->command('workers:sync_cdr')->everyMinute();
        $schedule->command('workers:set_is_work_time')->everyMinute();
        $schedule->command('workers:sync_pause')->hourly();


        $schedule->command(HoldMusic::class)
            ->everyMinute()
        ;
        $schedule->command(UnholdMusic::class)
            ->everyMinute()
        ;
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
