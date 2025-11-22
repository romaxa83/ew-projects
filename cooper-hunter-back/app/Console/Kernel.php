<?php

namespace App\Console;

use App\Console\Commands\Catalog\SeedCatalogCategories;
use App\Console\Commands\Commercial\CommercialProjectCheckStatusCommand;
use App\Console\Commands\Worker\RemoveOldData;
use App\Console\Commands\Worker\SyncOlmoProduct;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        SeedCatalogCategories::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('telescope:prune')
            ->everySixHours();

        $schedule->command(CommercialProjectCheckStatusCommand::class)
            ->dailyAt('00:00');

        $schedule->command(RemoveOldData::class)
            ->dailyAt('00:00');

        $schedule->command(SyncOlmoProduct::class)->everySixHours();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
