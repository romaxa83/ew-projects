<?php

namespace App\Console\Commands\Helpers\Telescope;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearTelescopeTables extends Command
{
    protected $signature = 'telescope:clear_tables';

    protected $description = 'Clear all telescope data.';

    public function handle()
    {
        try {
            $start = microtime(true);

            $this->exec();

            $time = microtime(true) - $start;

            $this->info("[helper] DONE [time = {$time}]");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    private function exec(): void
    {
        DB::table('telescope_entries')->truncate();
        DB::table('telescope_entries_tags')->truncate();
        DB::table('telescope_monitoring')->truncate();

        $this->info('Telescope data cleared successfully.');
    }
}
