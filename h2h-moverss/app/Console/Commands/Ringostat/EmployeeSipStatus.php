<?php

namespace App\Console\Commands\Ringostat;

use App\Services\Employees\SIPStatusService;
use Illuminate\Console\Command;

class EmployeeSipStatus extends Command
{
    protected $signature = 'ringostat:sip-status';

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
        $sipUsername = SIPStatusService::getOnline([
            11304,
            11314
        ])->getSipUsername();
        dd($sipUsername);

//        $this->service->getOnline();
    }
}

