<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

abstract class BaseCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    abstract public function exec(): void;

    public function handle()
    {
        try {
            $start = microtime(true);

            $this->exec();

            $time = microtime(true) - $start;

            $this->info("EXEC [{$this->signature}] Done [time = {$time}]");

            return self::SUCCESS;
        } catch (\Throwable $e) {
//            dd($e->getTrace());
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}
