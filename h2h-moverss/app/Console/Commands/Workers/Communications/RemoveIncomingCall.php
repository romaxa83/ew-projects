<?php

namespace App\Console\Commands\Workers\Communications;

use App\Models\Calls\IncomingCall;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class RemoveIncomingCall extends Command
{
    protected $signature = 'worker:remove-incoming-call';

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
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    public function exec()
    {
        $min = 60;
        $date = CarbonImmutable::now()->subMinutes($min);

        $res = IncomingCall::where('created_at', '<', $date)->delete();

        logger_info("[workers] REMOVE OLD INCOMING CALLS [$res]");
    }
}
