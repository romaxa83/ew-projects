<?php

namespace App\Console\Commands\Helpers\Asterisk;

use App\IPTelephony\Services\Client\Asterisk\AmiService;
use App\IPTelephony\Services\Client\Asterisk\AmiSocketClient;
use Illuminate\Console\Command;

class QueueStatus extends Command
{
    protected $signature = 'asterisk:queue_status';

    public function __construct(
//        protected AmiSocketClient $client
    )
    {
        parent::__construct();
    }

    public function handle()
    {
//        $this->client->connect();
//        $this->client->auth();
//        $this->client->write("Action: QueueStatus");
//        $this->client->listen();
//        $this->client->disconnect();

//        $data = $this->service->getQueueStatus();
//        dump($data);

    }
}

