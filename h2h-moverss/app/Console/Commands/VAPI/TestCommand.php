<?php

namespace App\Console\Commands\VAPI;

use App\Models\CashRegistry\CashRegistry;
use App\Models\CashRegistry\CashRegistryItem;
use App\Services\CashRegistry\CashRegistryService;
use App\Services\Requests\VAPI\Commands\Assistants\GetAssistants;
use App\Services\VAPI\VapiService;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $signature = 'vapi:test';

    public function __construct(
        protected VapiService $service
    )
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
        $callId = '34850c7b-eeae-44e7-9799-03e134c521b0';
        $res = $this->service->getCall($callId);

        dd($res);
    }
}

