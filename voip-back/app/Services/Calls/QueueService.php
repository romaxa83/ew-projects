<?php

namespace App\Services\Calls;

use App\Dto\Calls\QueueDto;
use App\Enums\Calls\QueueStatus;
use App\Enums\Calls\QueueType;
use App\Enums\Employees\Status;
use App\Models\Admins\Admin;
use App\Models\Calls\Queue;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use App\PAMI\Service\SendActionService;
use App\Repositories\Calls\QueueRepository;
use App\Repositories\Employees\EmployeeRepository;
use App\Services\AbstractService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use OpiyOrg\AriClient\Enum\ChannelStates;
use OpiyOrg\AriClient\Model\Channel;

class QueueService extends AbstractService
{
    const UN_KNOW_CONNECTED_NAME = 'unknown';

    public EmployeeRepository $employeeRepository;

    public function __construct()
    {
        $this->repo = resolve(QueueRepository::class);
        $this->employeeRepository = resolve(EmployeeRepository::class);
        return parent::__construct();
    }

    public function update(Queue $model, QueueDto $dto): Queue
    {
        logger_seq('[QueueService], [update] START');
        $model->caller_name = $dto->name;
        $model->case_id = $dto->caseID;
        $model->serial_number = $dto->serialNumber;
        $model->comment = $dto->comment;

        $model->save();

        return $model;
    }

    public function updateOrCreate(array $data)
    {
        logger_seq('[QueueService], [updateOrCreate] START');
        $tmp = [];
        logger_info('QUEUEU_UPDATE_FOR_QUEUE', $data);
        foreach ($data as $item){
//            if($model = $this->repo->getBy('uniqueid', data_get($item, 'uniqueid'))){
            if($model = $this->repo->getByFields([
                'uniqueid' => data_get($item, 'uniqueid'),
                'type' => QueueType::QUEUE,
            ])){
                logger_seq('[QueueService], [updateOrCreate] edit');
                $this->edit($model, $item);
            } else {
                logger_seq('[QueueService], [updateOrCreate] create');
                unset(
                    $item['update_connection'],
                    $item['update_called'],
                );
                $tmp[] = $item;
            }
        }

        if(!empty($tmp)){
            $this->creates($tmp);
        }
    }

    public function upsert(array $data, array $uniqFields)
    {
        Queue::upsert($data, $uniqFields);
    }

    public function edit(Queue $model, array $data)
    {
        logger_seq('[QueueService], [edit] START');

//        logger_info('QUEUEU_UPDATE', $data);

        if(!$model->serial_number){
            $model->serial_number = data_get($data, 'serial_number');
        }
        if(!$model->case_id){
            $model->case_id = data_get($data, 'case_id');
        }

        $model->employee_id = data_get($data, 'employee_id');
        $model->department_id = data_get($data, 'department_id');

        if(!$model->connected_at){
            logger_seq('[QueueService], [edit] connected_at');
            $model->connected_at = data_get($data, 'connected_at');
        }

        if(!$model->called_at){
            logger_seq('[QueueService], [edit] called_at');
            $model->called_at = data_get($data, 'called_at');
        }

        if(
            $model->connected_name !== 'unknown'
            && $model->connected_name != data_get($data, 'connected_name')
        ){
            logger_seq('[QueueService], [edit] null');
            $model->connected_at = null;
            $model->called_at = null;
        }

        if(
            (
                $model->connected_num !== data_get($data, 'connected_num')
                || $model->caller_name !== data_get($data, 'caller_num')
            )
            && $model->status->isTalk()
            && data_get($data, 'status') == QueueStatus::CONNECTION
        ){
            logger_seq('[QueueService], [edit] transfer');
            // звонок между агентами, и отвечавший трансферит звонок
            $model->connected_at = CarbonImmutable::now();
            $model->called_at = null;
        }

        $model->connected_name = data_get($data, 'connected_name');
        $model->connected_num = data_get($data, 'connected_num');

        $model->status = data_get($data, 'status');
        $model->wait = data_get($data, 'wait');
        $model->position = data_get($data, 'position');

        if(
            data_get($data, 'paused')
            && ($model->status->isWait() || $model->status->isConnection())
        ){
            logger_seq('[QueueService], [edit] wait');
            // для обновления из очереди
            $model->employee_id = null;
            $model->connected_name = null;
            $model->connected_num = null;
            $model->status = QueueStatus::WAIT();
            $model->connected_at = null;
        }

        $model->save();
    }

    public function transferToAgentOrDepartment(
        Admin|Employee $initiator,
        Employee|Department $target,
        Queue $queue
    ): bool
    {
        logger_seq('[QueueService], [transferToAgentOrDepartment] START');
        /** @var $sendService SendActionService */
        $sendService = resolve(SendActionService::class);

        $res = $sendService->QueueRedirect(
            $queue->channel,
            $this->generateExtension($initiator, $target)
        );

//        $res = $sendService->QueueWithdrawCaller(
//            $this->generateWithdrawinfo($initiator, $target),
//            $target instanceof Department
//                ? $queue->department?->name ?? null
//                : $queue->department?->name ?? null,
//            $queue->channel
//        );

        if($res){
            $data = [
                'status' => QueueStatus::CANCEL,
                'employee_id' => $target instanceof Employee
                    ? $target->id
                    : $queue->employee_id,
                'connected_num' => $target instanceof Employee
                    ? $target->sip->number
                    : $queue->connected_num,
                'connected_name' => $target instanceof Employee
                    ? $target->getName()
                    : $queue->connected_name,
                'connected_at' => null,
                'called_at' => null,
            ];

            $queue->update($data);

            logger_info('UPDATE QUEUE', $data);
        }

        return $res;
    }

    public function generateExtension(
        Admin|Employee $initiator,
        Employee|Department $target
    ): string
    {
        logger_seq('[QueueService], [generateExtension] START');
        $initUser = 'admin';
        if($initiator instanceof Employee){
            if($initiator->sip){
                $initUser = $initiator->sip->number;
            } else {
                $initUser = $initiator->guid;
            }
        }

        if($target instanceof Employee){
            $to = $target->sip->number;
        } else {
            $to = $target->num;
        }

        return "{$to}_{$initUser}";
    }

    public function generateWithdrawinfo(
        Admin|Employee $initiator,
        Employee|Department $target
    ): string
    {
        logger_seq('[QueueService], [generateWithdrawinfo] START');
        $initUser = 'admin';
        if($initiator instanceof Employee){
            if($initiator->sip){
                $initUser = $initiator->sip->number;
            } else {
                $initUser = $initiator->guid;
            }
        }

        if($target instanceof Employee){
            $to = $target->sip->number;
        } else {
            $to = $target->num;
        }

        return "CRM_TRANSFER,{$initUser},{$to}";
    }

    public function setStatusByField(string $field, string $value, QueueStatus $status): void
    {
        logger_seq('[QueueService], [setStatusByField] START');
        DB::table(Queue::TABLE)
            ->where($field, $value)
            ->update(['status' => $status]);

        logger_info("CHANGE QUEUE STATUS FROM CDR, [channel - {$value}], [status - $status]");
    }

    public function deleteAll(): int
    {
        return DB::table(Queue::TABLE)->delete();
    }

    // обрабатываем каналы от ari, для вывода звоноков между агентами
    public function handlerChannelFromAri(array $data)
    {
        logger_seq('[QueueService], [ari, handlerChannelFromAri] START');
        // если есть данные в канале, обрабатываем их
        if(!empty($data)){
            $normalizeData = [];

            foreach ($data as $key => $item){
                /** @var $item Channel */

                if(
                    $item->state == ChannelStates::DOWN
                    || str_contains($item->name, 'Local/')
                ){
                    logger_seq('[QueueService], [ari, handlerChannelFromAri] step 1');
                    unset($data[$key]);

                    DB::table(Queue::TABLE)
                        ->where('type', QueueType::DIAL)
                        ->where('uniqueid', $item->id)
                        ->delete();


                } else {
                    ////////////////////////////
                    if(
                        $item->accountcode == 'IS_FROM_QUEUE=false'
                        && $item->caller->number != ''
                        && $item->connected->number != ''
                    ){
                        logger_seq('[QueueService], [ari, handlerChannelFromAri] step 2');
                        // ari во время звонка, между агентами, создает два канала, нам нужен
                        // только тот которые является звонящим, сверяем по времени
                        $tmp = null;
                        foreach ($data as $k => $i){
                            /** @var $i Channel */
                            if(
                                $i->caller->number == $item->connected->number
                                && $i->connected->number == $item->caller->number
                            ){
                                $tmp = $i;
                            }
                        }

                        if($tmp){
                            logger_seq('[QueueService], [ari, handlerChannelFromAri] step 3');
                            $date_1 = CarbonImmutable::createFromTimeString($item->creationtime);
                            $date_2 = CarbonImmutable::createFromTimeString($tmp->creationtime);

                            $m = $date_1->lt($date_2) ? $item : $tmp;

                            // если у одного канала состояния up(взял трубку) а у другого ring(звонит телефон),
                            // то результирующему каналу проставляем статус ring
                            if (
                                ($item->state == ChannelStates::UP && ($tmp->state == ChannelStates::RINGING || $tmp->state == ChannelStates::RING))
                                || ($tmp->state == ChannelStates::UP && ($item->state == ChannelStates::RINGING || $item->state == ChannelStates::RING))
                            ) {
                                logger_seq('[QueueService], [ari, handlerChannelFromAri] step 4');
                                $m->state = ChannelStates::RINGING;
                            }

                            $normalizeData[$m->creationtime] = $m;
                        } else {
                            logger_seq('[QueueService], [ari, handlerChannelFromAri] step 5');
                            $normalizeData[$item->creationtime] = $item;
                        }
                    } elseif (
                        $item->accountcode == 'IS_FROM_QUEUE=false'
                        && $item->connected->name == ''
                        && $item->connected->number == ''
                    ){
                        logger_seq('[QueueService], [ari, handlerChannelFromAri] step 6');
                        // звонивший позвонил на очередь, но еще не распределился
                        $normalizeData[] = $item;

                    } elseif ($item->accountcode == 'IS_FROM_QUEUE=true'){
                        // если звонок перешел в очередь, данную запись убираем, т.к. эта запись выведется через ami
                        DB::table(Queue::TABLE)
                            ->where('type', QueueType::DIAL)
                            ->where('uniqueid', $item->id)
                            ->update(['status' => QueueStatus::CANCEL])
                        ;
                        logger_seq('[QueueService], [ari, handlerChannelFromAri] step 7');
                    }
                    //////////////////////////////
                }
            }

            // получаем пользователей по c sip номерам
            $employees = $this->employeeRepository->getEmployeeDataBySips();

            $recData = [];
            foreach ($normalizeData as $datum){
                /** @var $datum Channel */

                if($datum->connected->number == '' && $datum->connected->name == ''){
                    logger_seq('[QueueService], [ari, handlerChannelFromAri] prepare data 1');
                    $recData[] = [
                        'position' => 0,
                        'wait' => CarbonImmutable::createFromTimeString($datum->creationtime, 'UTC')
                            ->diffInSeconds(CarbonImmutable::now()),
                        'employee_id' => null,
                        'department_id' => null,
                        'caller_num' => $datum->caller->number,
                        'caller_name' => $datum->caller->name,
                        'connected_num' => null,
                        'connected_name' => null,
                        'status' =>  QueueStatus::WAIT,
                        'uniqueid' => $datum->id,
                        'channel' => $datum->name,
                        'connected_at' => null,
                        'type' => QueueType::DIAL,
                        'called_at' => null,
                    ];
                } else {
                    $employeeID = null;
                    $departmentID = null;
                    if(isset($employees[$datum->connected->number])){
                        $employeeID = $employees[$datum->connected->number]->employee_id;
                        $departmentID = $employees[$datum->connected->number]->department_id;
                    } elseif(isset($employees[$datum->caller->number])){
                        $employeeID = $employees[$datum->caller->number]->employee_id;
                        $departmentID = $employees[$datum->caller->number]->department_id;
                    }
                    logger_seq('[QueueService], [ari, handlerChannelFromAri] prepare data 2');
                    $recData[] = [
                        'position' => 0,
                        'wait' => 0,
                        'employee_id' => $employeeID,
                        'department_id' => $departmentID,
                        'caller_num' => $datum->caller->number,
                        'caller_name' => $datum->caller->name,
                        'connected_num' => $datum->connected->number,
                        'connected_name' => $datum->connected->name,
                        'status' => $datum->state === ChannelStates::UP
                            ? QueueStatus::TALK
                            : QueueStatus::CONNECTION,
                        'uniqueid' => $datum->id,
                        'channel' => $datum->name,
//                        'connected_at' => CarbonImmutable::createFromTimeString($datum->creationtime, 'UTC'),
                        'connected_at' => CarbonImmutable::now(),
                        'type' => QueueType::DIAL,
                        'called_at' => $datum->state === ChannelStates::UP
                            ? CarbonImmutable::now()
                            : null,
                    ];
                }
            }

            $this->updateOrCreateForDial($recData);
        } else {
            // если данных в канале нет, проверяем что не осталось висящих каналов
            if(
                DB::table(Queue::TABLE)
                    ->where('status', '!=', QueueStatus::CANCEL)
                    ->exists()
            ){
                DB::table(Queue::TABLE)
                    ->where('status', '!=', QueueStatus::CANCEL)
                    ->update(['status' => QueueStatus::CANCEL]);
            }
            logger_seq('[QueueService], [ari, handlerChannelFromAri] step 8');

        }
    }

    public function updateOrCreateForDial(array $data)
    {
        logger_info('CREATE OR UPDATE DIAL', $data);
        logger_seq('[QueueService], [ari, updateOrCreateForDial] START');
        $tmp = [];
        foreach ($data as $item){
            // если есть уже запись об это звонки из очереди (дубликат), то не обновляем запись
            if(
                !(DB::table(Queue::TABLE)
                    ->where('type', QueueType::QUEUE())
                    ->where('caller_num', data_get($item, 'caller_num'))
                    ->where('connected_num', data_get($item, 'connected_num'))
                    ->where('status', '!=', QueueStatus::CANCEL)
                    ->exists()
                ||
                DB::table(Queue::TABLE)
                    ->where('type', QueueType::QUEUE())
                    ->where('caller_num', data_get($item, 'connected_num'))
                    ->where('connected_num', data_get($item, 'caller_num'))
                    ->where('status', '!=', QueueStatus::CANCEL)
                    ->exists())
            ){
                if($model = $this->repo->getByFields([
                    'uniqueid' => data_get($item, 'uniqueid'),
                    'type' => QueueType::DIAL,
                ])){
                    logger_seq('[QueueService], [ari, updateOrCreateForDial] edit');
                    $this->edit($model, $item);
                } else {
                    logger_seq('[QueueService], [ari, updateOrCreateForDial] create');
                    $tmp[] = $item;
                }
            }
        }

        if(!empty($tmp)){
            $this->creates($tmp, QueueType::DIAL);
        }
    }

    public function creates($data, $type = QueueType::QUEUE)
    {
        logger_seq('[QueueService], [ari, creates] START ' . $type);
        foreach ($data as $item){
            $item['type'] = $type;
            $this->create($item);
        }
    }

    public function create(array $data)
    {
        logger_info("CREATE QUEUE", $data);
        $model = new Queue();

        $model->serial_number = data_get($data, 'serial_number');
        $model->case_id = data_get($data, 'case_id');
        $model->employee_id = data_get($data, 'employee_id');
        $model->department_id = data_get($data, 'department_id');
        $model->connected_at = data_get($data, 'connected_at');
        $model->called_at = data_get($data, 'called_at');
        $model->caller_name = data_get($data, 'caller_name');
        $model->caller_num = data_get($data, 'caller_num');
        $model->connected_name = data_get($data, 'connected_name');
        $model->connected_num = data_get($data, 'connected_num');
        $model->status = data_get($data, 'status');
        $model->wait = data_get($data, 'wait');
        $model->position = data_get($data, 'position');
        $model->uniqueid = data_get($data, 'uniqueid');
        $model->channel = data_get($data, 'channel');
        $model->type = data_get($data, 'type');

        $model->save();
    }
}
