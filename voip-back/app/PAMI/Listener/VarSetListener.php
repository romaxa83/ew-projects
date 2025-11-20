<?php

namespace App\PAMI\Listener;

use App\Enums\Calls\QueueStatus;
use App\Models\Calls\Queue;
use App\PAMI\Message\Event\EventMessage;
use App\PAMI\Message\Event\VarSetEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class VarSetListener implements IEventListener
{
    public function handle(EventMessage $event): void
    {
        $this->setStatusToEmployee($event);
    }

    protected function setStatusToEmployee(EventMessage $event)
    {
        if($event instanceof VarSetEvent){

            if($event->isCaseId()){
                logger_info($event->getName(), [
                    'VARIABLE' => $event->getVariableName(),
                    'VALUE' => $event->getValue(),
                    'CHANNEL' => $event->getChannel(),
                ]);

                Cache::tags('queue_call')->remember(
                    VarSetEvent::CASE_ID.'_'. $event->getChannel(),
                    30,
                    fn() => $event
                );
            }

            if($event->isSerialNumber()){
                logger_info($event->getName(), [
                    'VARIABLE' => $event->getVariableName(),
                    'VALUE' => $event->getValue(),
                    'CHANNEL' => $event->getChannel(),
                ]);

                Cache::tags('queue_call')->remember(
                    VarSetEvent::SERIAL_NUMBER.'_'. $event->getChannel(),
                    30,
                    fn() => $event
                );
            }

            if($event->isDestroy()){
                logger_info($event->getName(), [
                    'VARIABLE' => $event->getVariableName(),
                    'VALUE' => $event->getValue(),
                    'CHANNEL' => $event->getChannel(),
                ]);

                if(
                    DB::table(Queue::TABLE)
                        ->where('channel', $event->getChannel())
                        ->update([
                            'status' => QueueStatus::CANCEL
                        ])
                ){
                    logger_info("CHANGE QUEUE STATUS FROM VarSetListener, [channel - {$event->getChannel()}], [status - cancel]");
                }
            }
        }
    }
}
