<?php

namespace App\PAMI\Listener;

use App\Enums\Calls\QueueStatus;
use App\Enums\Calls\QueueType;
use App\GraphQL\Types\Wrappers\PaginationMeta;
use App\Models\Calls\Queue;
use App\PAMI\Message\Event\DialEndEvent;
use App\PAMI\Message\Event\DialStateEvent;
use App\PAMI\Message\Event\EventMessage;
use App\Repositories\Employees\EmployeeRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class DialListener implements IEventListener
{
    public function handle(EventMessage $event): void
    {
        $this->listenDialForQueue($event);
    }

    protected function listenDialForQueue(EventMessage $event)
    {
        if($event instanceof DialStateEvent){

            logger_seq('[PAMI][DialListener], [listenDialForQueue] DialStateEvent');

            logger_info($event->getName(), [
                'CHANNEL' => $event->getChannel(),
                'STATUS' => $event->getDialStatus(),
                'DestConnectedLineNum' => $event->getDestConnectedLineNum(),
                'ConnectedLineNum' => $event->getConnectedLineNum(),
                'ConnectedLineName' => $event->getConnectedLineName(),
            ]);

            if($event->isRingingStatus()) {
                logger_seq('[PAMI][DialListener], [listenDialForQueue] DialStateEvent ring');
                /** @var $repo EmployeeRepository */
                $repo = resolve(EmployeeRepository::class);
                $sipIds = $repo->getEmployeeIdAdnSipNumber();

                $tmp = [];
                if(isset($sipIds[$event->getConnectedLineNum()])){
                    $tmp['employee_id'] = $sipIds[$event->getConnectedLineNum()];
                    $tmp['department_id'] = $repo->getByFieldsObj(['id' => $tmp['employee_id']], ['department_id'])?->department_id;
                }

                if(
                    DB::table(Queue::TABLE)
                        ->where('channel', $event->getChannel())
                        ->where('type', QueueType::QUEUE)
                        ->update(array_merge($tmp, [
                            'status' => QueueStatus::CONNECTION,
                            'connected_num' => $event->getConnectedLineNum(),
                            'connected_name' => $event->getConnectedLineName(),
                            'connected_at' => CarbonImmutable::now(),
                            'called_at' => null,
                        ]))
                ){
                    logger_seq('[PAMI][DialListener], [listenDialForQueue] DialStateEvent upadte');
                    logger_info("DIAL_STATE_EVENT, Change queue [channel - {$event->getChannel()}], status to connection");
                }
            }
        }

        if($event instanceof DialEndEvent){
            logger_info($event->getName(), [
                'CHANNEL' => $event->getChannel(),
                'STATUS' => $event->getDialStatus(),
            ]);
            logger_seq('[PAMI][DialListener], [listenDialForQueue] DialEndEvent');
            if($event->isStatusAnswer() && DB::table(Queue::TABLE)
                    ->where('channel', $event->getChannel())
                    ->where('type', QueueType::QUEUE)
                    ->update([
                        'status' => QueueStatus::TALK,
                        'called_at' => CarbonImmutable::now(),
                    ])){

                logger_seq('[PAMI][DialListener], [listenDialForQueue] DialEndEvent update');

                logger_info("DIAL_END_EVENT, Change queue [channel - {$event->getChannel()}], status to talk");
            }
//            else {
//                DB::table(Queue::TABLE)
//                    ->where('channel', $event->getChannel())
//                    ->update([
//                        'status' => QueueStatus::WAIT(),
//                        'called_at' => null,
//                        'connected_at' => null,
//                        'connected_num' => null,
//                        'connected_name' => null,
//                        'employee_id' => null,
//                        'department_id' => null,
//                    ]);
//
//                logger_info("DIAL_END_EVENT, Change queue [channel - {$event->getChannel()}], status to wait");
//            }
        }
    }
}

