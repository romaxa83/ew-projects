<?php

namespace App\Console\Commands\Worker;

use Illuminate\Console\Command;

class TestWorker extends Command
{
    protected $signature = 'jd:worker-test';

    public function handle()
    {
        \Log::notice("TEST WORKER");
    }
}

