<?php

namespace App\Console\Commands\Helpers\Websocket;

use App\Events\Communications\EmployeeStatus;
use App\Events\Ringostat\EmployeeOnCall;
use App\Models\Employee;
use Illuminate\Console\Command;

class Test extends Command
{
    protected $signature = 'websocket:test';

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

            echo PHP_EOL;
            $this->info("Done [time = {$time}]");
            echo PHP_EOL;

            return self::SUCCESS;
        } catch (\Throwable $e) {
            dd($e);
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    public function exec()
    {
        broadcast(new EmployeeStatus(Employee::first()));
    }
}



