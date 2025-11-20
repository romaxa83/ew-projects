<?php

namespace App\Console\Commands\Helpers\PAMI;

use App\IPTelephony\Services\Storage\Asterisk\QueueMemberService;
use App\PAMI\Message\Action\QueuePauseAction;
use App\PAMI\Message\Action\QueueStatusAction;
use App\PAMI\Message\Action\QueueUnpauseAction;
use Exception;
use Illuminate\Console\Command;
use App\PAMI\Client\Impl\ClientAMI;

class QueueStatus extends Command
{
    protected $signature = 'pami:queue_status';

    protected $description = '';

    public function __construct(
        protected ClientAMI $client
    )
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $command = new QueueStatusAction();

            $this->client->open();

            $res = $this->client->send($command);

            dd($res);

//            logger_info("For queueMember[{$this->queueMember->membername}] has message - {$res->getKey('message')}");

            $this->client->close();


        } catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }
}

