<?php

namespace App\Console\Commands\Zadarma;

use App\Http\Controllers\Zadarma\PBXController;
use App\Models\Division;
use App\Services\Employees\CommunicationStatusService;
use Illuminate\Console\Command;

class Sync extends Command
{
    protected $signature = 'zadarma:sync_sip_status';

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

    public function exec(): void
    {
        $this->service->updateSipStatus();
//        $this->service->zadarmaSipStatus();

//        $this->forTest();

    }

    public function forTest(): void
    {
        $division  = Division::find(1);

        $key = $division->miscs['zadarma_api_key'];
        $secret = $division->miscs['zadarma_api_secret'];

        /** @var $client \Zadarma_API\Api */
        $client = (new PBXController())->getAPI($key, $secret);

//        $this->service->zadarmaSipStatus($division);

//        dd($client->getWebhookEvent('105'));
//        dd($client->getPbxInfo('101'));
        dd($client->getPbxStatus('101'));
        dd($client->getPbxInternal());
        dd($client->getSipStatus('722633'));
        dd($client->getSip());
    }
}
