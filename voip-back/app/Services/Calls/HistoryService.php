<?php

namespace App\Services\Calls;

use App\Dto\Calls\HistoryDto;
use App\Enums\Calls\QueueType;
use App\IPTelephony\Services\Storage\Asterisk\CdrService;
use App\IPTelephony\Services\Storage\Asterisk\QueueLogService;
use App\Models\Calls\History;
use App\Models\Calls\Queue;
use App\Models\Employees\Employee;
use App\Repositories\Calls\QueueRepository;
use App\Repositories\Employees\EmployeeRepository;

class HistoryService
{
    public function __construct(
        protected QueueRepository $queueRepository,
        protected EmployeeRepository $employeeRepository,
    )
    {}

    public function create(HistoryDto $dto): History
    {
        /** @var $queue Queue */
        $queue = $this->queueRepository->getByFields([
            'channel' => $dto->channel,
            'type' => QueueType::QUEUE,
        ]);

        $employeeIdAdnSipNumber = $this->employeeRepository->getEmployeeIdAdnSipNumber();

        // если нет id пользователя, но в dialed есть сип пользователя, по сипу получаем его id
//        if(!$dto->employeeID && $dto->dialed){
//            if(isset($employeeIdAdnSipNumber[$dto->dialed])){
//                $dto->employeeID = $employeeIdAdnSipNumber[$dto->dialed];
//            }
//        }

        if(array_key_exists($dto->dialed, $employeeIdAdnSipNumber)){
            $dto->employeeID = $employeeIdAdnSipNumber[$dto->dialed];
            $dto->dialedName = null;
        }

        // если нет id пользователя, но в dialed есть сип пользователя, по сипу получаем его id
        if(!$dto->fromEmployeeID && $dto->fromNum){
            if(isset($employeeIdAdnSipNumber[$dto->fromNum])){
                $dto->fromEmployeeID = $employeeIdAdnSipNumber[$dto->fromNum];
            }
        }

        /** @var $employee Employee */
        $employee = $this->employeeRepository->getBy('id', $dto->employeeID);

        $model = new History();

        $model->employee_id = $dto->employeeID;
        $model->from_employee_id = $dto->fromEmployeeID;
        $model->department_id = $dto->departmentID;
        $model->status = $dto->status;
        $model->from_num = $dto->fromNum;
        $model->from_name = $dto->fromName;
        $model->from_name_pretty = CdrService::parseNameFromClid($dto->fromName);
        $model->dialed = $dto->dialed;

        if($dto->dialed !== 'admin' && $dto->dialedName === null){
            $model->dialed_name = $employee?->getName();
        } else {
            $model->dialed_name = $dto->dialedName;
        }

        $model->duration = $dto->duration;
        $model->billsec = $dto->billsec;
        $model->lastapp = $dto->lastapp;
        $model->uniqueid = $dto->uniqueid;
        $model->channel = $dto->channel;
        $model->call_date = $dto->callDate;

        if(!$dto->serialNumbers && $queue){
            $model->serial_numbers = $queue->serial_number;
        } else {
            $model->serial_numbers = $dto->serialNumbers;
        }
        if(!$dto->caseID && $queue){
            $model->case_id = $queue->case_id;
        } else {
            $model->case_id = $dto->caseID;
        }
        if($queue){
            $model->comment = $queue->comment;
            $model->from_name_pretty = $queue->caller_name;
        }

        $model->save();

        // запускаем обработку данных для отчета
        /** @var $queueLogService QueueLogService */
        $queueLogService = resolve(QueueLogService::class);
        $queueLogService->uploadData($dto->uniqueid);

        return $model;
    }
}

