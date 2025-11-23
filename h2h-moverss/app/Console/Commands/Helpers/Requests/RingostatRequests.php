<?php

namespace App\Console\Commands\Helpers\Requests;

use App\Models\Division;
use App\Services\Employees\CommunicationStatusService;
use Illuminate\Console\Command;

class RingostatRequests extends Command
{
    protected $signature = 'request:ringostat';

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
        $division = Division::query()->where('short', 'IL')->first();

        $this->service->ringostatSipStatus($division);
    }
}



