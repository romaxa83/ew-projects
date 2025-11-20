<?php

namespace App\PAMI\Listener;

use App\Enums\Calls\QueueStatus;
use App\Models\Calls\Queue;
use App\PAMI\Message\Event\EventMessage;
use App\PAMI\Message\Event\HangupEvent;
use Illuminate\Support\Facades\DB;

class HangupListener implements IEventListener
{
    public function handle(EventMessage $event): void
    {
        $this->listenDialForQueue($event);
    }

    protected function listenDialForQueue(EventMessage $event)
    {
        if($event instanceof HangupEvent){
            logger_seq('[PAMI][HangupListener], [listenDialForQueue] HangupEvent');

            logger_info($event->getName(), [
                'CHANNEL' => $event->getChannel(),
            ]);

            if(
                DB::table(Queue::TABLE)
                    ->where('channel', $event->getChannel())
                    ->update([
                        'status' => QueueStatus::CANCEL,
                    ])
            ){
                logger_seq('[PAMI][HangupListener], [listenDialForQueue] HangupEvent update');

                logger_info("HANGUP_EVENT, Change queue [channel - {$event->getChannel()}], status to cancel");
            }
        }
    }
}
