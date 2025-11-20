<?php

namespace App\Console\Commands\Helpers\PAMI;

use App\IPTelephony\Services\Storage\Asterisk\QueueMemberService;
use App\PAMI\Message\Action\QueuePauseAction;
use App\PAMI\Message\Action\QueueUnpauseAction;
use Exception;
use Illuminate\Console\Command;
use App\PAMI\Client\Impl\ClientAMI;

class PausedQueueMember extends Command
{
    protected $signature = 'pami:member_paused {--uuid=} {--p=}';

    protected $description = 'Ставит или снимает с паузы сотрудника в asterisk';

    protected object $queueMember;
    protected bool $pause;

    public function __construct(
        protected ClientAMI $client,
        protected QueueMemberService $queueMemberService
    )
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $this->init();

            if($this->pause){
                $command = new QueuePauseAction($this->queueMember->interface);
            } else {
                $command = new QueueUnpauseAction($this->queueMember->interface);
            }

            $this->client->open();

            $res = $this->client->send($command);

            logger_info("For queueMember[{$this->queueMember->membername}] has message - {$res->getKey('message')}");

            $this->client->close();


        } catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }

    public function init(): void
    {
        $uuid = $this->option('uuid');
        if(!$uuid){
            $uuid = $this->ask('Entity uuid employee');
        }

        $p = $this->option('p');
        if(!$p){
            $p = $this->ask('Paused');
        }

        $this->queueMember = $this->queueMemberService->getBy('uuid', $uuid);
        if($this->queueMember === null){
            throw new \InvalidArgumentException("Not found queue member by [uuid = {$uuid}]");
        }

        $this->pause = $p == 'true' ? true: false;
    }
}
