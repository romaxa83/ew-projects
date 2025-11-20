<?php

namespace App\PAMI\Listener;

use App\Enums\Calls\QueueStatus;
use App\Enums\Employees\Status;
use App\Models\Calls\Queue;
use App\Models\Employees\Employee;
use App\Models\Sips\Sip;
use App\PAMI\Message\Event\EventMessage;
use App\PAMI\Message\Event\QueueMemberStatusEvent;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class QueueStatusListener implements IEventListener
{
    public function handle(EventMessage $event): void
    {
        $this->setStatusToEmployee($event);
    }

    protected function setStatusToEmployee(EventMessage $event)
    {
        if($event instanceof QueueMemberStatusEvent){
            logger_seq('[PAMI][QueueStatusListener], [setStatusToEmployee] QueueMemberStatusEvent');
            logger_info('[employee-status] '. $event->getName(), [
//                'Privilege' => $event->getPrivilege(),
                'Queue' => $event->getQueue(),
                'MemberName' => $event->getMemberName(),
                'Membership' => $event->getMembership(),
//                'Penalty' => $event->getPenalty(),
                'CallsTaken' => $event->getCallsTaken(),
                'Status' => $event->getStatus(),
                'Paused' => $event->getPaused(),
            ]);

            $start = microtime(true);

                $status = match ($event->getStatus()) {
                    1 => Status::FREE,
                    2 => Status::TALK,
                    default => null
                };
                if($event->getPaused()){
                    $status = Status::PAUSE;
                }

                if($status){
                    logger_seq('[PAMI][QueueStatusListener], [setStatusToEmployee] QueueMemberStatusEvent has status');
                    DB::table(Employee::TABLE)
                        ->select(
                            Employee::TABLE. '.id as employee_id',
                            Employee::TABLE. '.status',
                            Sip::TABLE. '.number'
                        )
                        ->join(Sip::TABLE, function (JoinClause $join) use($event) {
                            $join->on(Employee::TABLE.'.sip_id', '=', Sip::TABLE.'.id')
                                ->where(Sip::TABLE.'.number',  $event->getMemberName());
                        })
                        ->update(['status' => $status]);

                    logger_info("[employee-status] Employee [{$event->getMemberName()}] set status [{$status}]");

                    if($status == Status::TALK){
                        logger_seq('[PAMI][QueueStatusListener], [setStatusToEmployee] QueueMemberStatusEvent status talk');
                        if(
                            DB::table(Queue::TABLE)
                                ->where('connected_num', $event->getMemberName())
                                ->where('status', QueueStatus::CONNECTION)
                                ->update([
                                    'status' => QueueStatus::TALK,
                                    'called_at' => CarbonImmutable::now(),
                                    'in_call' => 1
                                ])
                        ){
                            logger_info("[employee-status] CHANGE QUEUE STATUS FROM QueueStatusListener, [connected_num - {$event->getMemberName()}], [status - talk]");
                        }

                    }
//                    if($status == Status::FREE){
//                        if(
//                            DB::table(Queue::TABLE)
//                                ->where('connected_num', $event->getMemberName())
////                                ->where('status', QueueStatus::TALK)
//                                ->update([
//                                    'status' => QueueStatus::CANCEL
//                                ])
//                        ){
//                            logger_info("CHANGE QUEUE STATUS FROM QueueStatusListener, [connected_num - {$event->getMemberName()}], [status - cancel]");
//                        }
//                    }
                }

            $time = microtime(true) - $start;
            logger_info("[employee-status] Time [{$time}]");
        }
    }
}

