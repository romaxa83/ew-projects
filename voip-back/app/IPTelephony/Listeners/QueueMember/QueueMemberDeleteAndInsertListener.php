<?php

namespace App\IPTelephony\Listeners\QueueMember;

use App\IPTelephony\Events\QueueMember\QueueMemberDeleteAndInsertEvent;
use App\IPTelephony\Services\Storage\Asterisk\QueueMemberService;

class QueueMemberDeleteAndInsertListener
{
    public function __construct(protected QueueMemberService $service)
    {}

    public function handle(QueueMemberDeleteAndInsertEvent $event): void
    {
        if(config('app.enable_asterisk_kamailio')){
            try {

                if($this->service->remove($event->getModel())){
                    $this->service->create($event->getModel());
                    logger_info("INSERT queue member [asterisk] SUCCESS [{$event->getModel()->id}]");
                }

                logger_info("DELETE AND INSERT queue member [asterisk] SUCCESS");
            } catch (\Throwable $e) {
                logger_info("DELETE AND INSERT queue member [asterisk] FAILED", [
                    'message' => $e->getMessage()
                ]);
            }
        }
    }
}

