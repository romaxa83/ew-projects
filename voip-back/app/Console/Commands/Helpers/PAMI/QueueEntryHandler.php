<?php

namespace App\Console\Commands\Helpers\PAMI;

use App\IPTelephony\Services\Storage\Asterisk\CdrService;
use App\PAMI\Listener\VarSetListener;
use App\PAMI\Message\Event\QueueEntryEvent;
use App\PAMI\Message\Event\QueueMemberEvent;
use App\PAMI\Message\Event\VarSetEvent;
use App\Repositories\Departments\DepartmentRepository;
use App\Repositories\Employees\EmployeeRepository;
use App\Services\Calls\QueueService;
use Carbon\CarbonImmutable;
use Exception;
use App\PAMI\Client\Impl\ClientAMI;
use App\PAMI\Message\Action\QueueStatusAction;
use Illuminate\Support\Facades\Cache;

class QueueEntryHandler
{
    protected array $departmentList;
    protected array $employeeList;
    protected QueueService $callQueueService;

    public function __construct(
        protected ClientAMI $client
    )
    {
        /** @var $departmentRepo DepartmentRepository */
        $departmentRepo = resolve(DepartmentRepository::class);
        /** @var $employeeRepo EmployeeRepository */
        $employeeRepo = resolve(EmployeeRepository::class);
        /** @var $callQueueService QueueService */
        $this->callQueueService = resolve(QueueService::class);

        $this->departmentList = $departmentRepo->getDepartmentIdsAndName();
        $this->employeeList = $employeeRepo->getEmployeeIdAdnSipNumber();
    }

    public function run()
    {
        try {
            $command = new QueueStatusAction();

            while (true){
                $res = $this->client->send($command);

                $this->handler($res->getEvents());

                sleep(2);
            }
        } catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }

    public function handler(array $data)
    {
        $tmp = [];
        $uniq = [];
        foreach ($data as $item){
            if($item instanceof QueueEntryEvent){
                logger_seq('[PAMI][QueueEntryHandler], [handler] QueueEntryEvent');
                // обрабатываем только те записи, у которых есть наш департамент (сравнение по названию отдела)
                if(array_key_exists($item->getQueue(), $this->departmentList)){
                    logger_seq('[PAMI][QueueEntryHandler], [handler] QueueEntryEvent department null timer');
                    $additional = [];
                    /**
                     * получаем события VarSetEvent из которых берем serialNumber и casId для данной записи, если они записаны в кеш ранее
                     * записываются в кеш здесь - @see VarSetListener, слушатель
                     * подключается здесь - @see \App\Console\Commands\Helpers\PAMI\Listener
                     */
                    $sn = Cache::tags('queue_call')
                        ->get(VarSetEvent::SERIAL_NUMBER.'_'. $item->getChannel());
                    $additional['serial_number'] = $sn ? $sn->getValue() : null ;
                    $case = Cache::tags('queue_call')
                        ->get(VarSetEvent::CASE_ID.'_'. $item->getChannel());
                    $additional['case_id'] = $case ? $case->getValue() : null ;

                    $status = \App\Enums\Calls\QueueStatus::WAIT();
                    $connected_at = null;
                    $called_at = null;
                    $employee_id = null;
                    $inCall = 0;
                    $paused = false;

                    // проверяем по QueueMember(агенты), и смотрим есть ли соединение с текущим клиентом
                    foreach ($data as $event){
                        if($event instanceof QueueMemberEvent){
                            logger_seq('[PAMI][QueueEntryHandler], [handler] QueueMemberEvent');
//                            logger_info($event->getName(), [$event->getRawContent()]);

                            if(CdrService::parseNumFromChanel($event->getLocation()) == $item->getConnectedLineNum()){
                                logger_seq('[PAMI][QueueEntryHandler], [handler] QueueMemberEvent check');
                                if($event->getStatus() == QueueMemberEvent::STATUS_AST_DEVICE_RINGING){
                                    logger_info('CONNECTION QUEUE MEMBER', [$event->getRawContent()]);
                                    logger_seq('[PAMI][QueueEntryHandler], [handler] QueueMemberEvent check STATUS_AST_DEVICE_RINGING');
                                    $status = \App\Enums\Calls\QueueStatus::CONNECTION();
                                    $connected_at = CarbonImmutable::now();
                                }

                                $inCall = $event->getInCall();
                                $paused = $event->getPaused();
                                $employee_id = $this->employeeList[$item->getConnectedLineNum()] ?? null;
                            }
                        }
                    }

                    $res = array_merge($additional,[
                        'department_id' => $this->departmentList[$item->getQueue()],
                        'employee_id' => $employee_id,
                        'caller_num' => $item->getCallerIDNum(),
                        'caller_name' => $item->getCallerIDName(),
                        'connected_num' => $item->getConnectedLineNum(),
                        'connected_name' => $item->getConnectedLineName(),
                        'position' => $item->getPosition(),
                        'wait' => $item->getWait(),
                        'channel' => $item->getChannel(),
                        'uniqueid' => $item->getUniqueid(),
                        'status' => $status,
                        'connected_at' => $connected_at,
                        'called_at' => $called_at,
                        'in_call' => $inCall,
                        'paused' => $paused,
                    ]);

                    $tmp[] = $res;

                    logger_info("SET CALL QUEUE - [department = {$item->getQueue()},", $res, false);
                }
            }
        }

        if(!empty($tmp)){
            $this->callQueueService->updateOrCreate($tmp);
        }
    }
}
