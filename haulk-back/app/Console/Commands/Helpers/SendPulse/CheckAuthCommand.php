<?php

namespace App\Console\Commands\Helpers\SendPulse;

use App\Services\SendPulse\Commands\RequestCommand;
use App\Services\SendPulse\Commands\AuthCommand;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckAuthCommand extends Command
{
    protected $signature = 'sendpulse:auth';

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

            logger_info("[helper] ".__CLASS__." [time = {$time}]");
            $this->info("[helper] ".__CLASS__." [time = {$time}]");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            logger_info("[helper] ".__CLASS__." FAIL", [
                'msg' => $e->getMessage()
            ]);
            return self::FAILURE;
        }
    }

    private function exec(): void
    {
        /** @var $command RequestCommand */
        $command = resolve(AuthCommand::class);
        $command->handler();
    }
}
