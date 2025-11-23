<?php

namespace App\Console\Commands\Ringostat;

use App\Services\Employees\CommunicationStatusService;
use Illuminate\Console\Command;

class EmployeeStatus extends Command
{
    protected $signature = 'ringostat:status-employees';

    public function __construct(protected CommunicationStatusService $service)
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
        $this->service->ringostatSipStatus();
    }
}

