<?php

namespace App\Console\Commands\FixDB;

use DB;
use Illuminate\Console\Command;

class UnitSetSeq extends Command
{
    protected $signature = 'fix_db:unit_set_seq';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $start = microtime(true);

            $this->exec();

            $time = microtime(true) - $start;

            $this->info("Done [time = {$time}]");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    private function exec()
    {
        DB::statement("SELECT setval('inventory_units_id_seq', (SELECT MAX(id) FROM inventory_units))");
    }
}

