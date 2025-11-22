<?php

namespace App\Console;

use App\Console\Commands\Admins\CreateAdminCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        CreateAdminCommand::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('telescope:prune')->daily();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
