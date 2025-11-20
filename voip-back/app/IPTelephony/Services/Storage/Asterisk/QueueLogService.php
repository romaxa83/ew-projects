<?php

namespace App\IPTelephony\Services\Storage\Asterisk;

use App\Dto\Reports\ReportItemDto;
use App\Enums\Reports\ReportStatus;
use App\IPTelephony\Entities\Asterisk\QueueLogEntity;
use App\Repositories\Departments\DepartmentRepository;
use App\Repositories\Employees\EmployeeRepository;
use App\Repositories\Reports\ReportRepository;
use App\Services\Reports\ReportItemService;
use App\Services\Reports\ReportPauseItemService;
use App\Services\Reports\ReportService;
use Illuminate\Database\Query\Builder;

class QueueLogService extends AsteriskService
{
    public const RING_NO_ANSWER_MILLISECONDS = 5000;

    public function __construct(
        protected EmployeeRepository $employeeRepository,
        protected DepartmentRepository $departmentRepository,
        protected ReportRepository $reportRepository,
        protected ReportService $reportService,
        protected ReportItemService $reportItemService,
        protected ReportPauseItemService $reportPauseItemService,
    )
    {}

    public function getTable(): string
    {
        return QueueLogEntity::TABLE;
    }

    public function uploadData($uniqueId = null)
    {
        try {
            $departmentsIDs = $this->departmentRepository->getDepartmentIdsAndName();

            $reportIDs = $this->reportRepository->getReportIdAdnSipNumber();

//            $departmentsIDs = [
//                'Sales Department_test' => 12
//            ];

            $countUpload = 0;
            $countFetch = 0;
            $countIgnore = 0;
            foreach ($departmentsIDs as $name => $id){
                $data = [];

                $this->getConectionDb()
                    ->where('queuename', $name)
                    ->when($uniqueId, fn($q) => $q->where('callid', $uniqueId))
                    ->whereNotIn('callid', QueueLogEntity::withoutCallid())
                    ->where(function (Builder $q){
                        return $q->whereNotIn('is_fetch', [1, 2])
                            ->orWhereNull('is_fetch');
                    })
                    ->orderBy('time')
                    ->get()
                    ->each(function ($item) use (&$data) {
                        $item = stdToArray($item);
                        $data[$item['callid']][] = $item;
                    });

//                logger_info('REPORTS DATA', $data);

                $recs = [];
                $fetch = [];
                $ignore = [];
                foreach ($data as $key => $datum){
                    $events = array_unique(array_column($datum, 'event'));

                    if(
                        // case 1
                        // звонок пришел (ENTERQUEUE), законектился с агентом (CONNECT) и
                        // был завершен или агентом (COMPLETEAGENT) или клиентом (COMPLETECALLER)
                        count($events) == 3
                        && in_array(QueueLogEntity::ENTERQUEUE, $events)
                        && in_array(QueueLogEntity::CONNECT, $events)
                        && (
                            in_array(QueueLogEntity::COMPLETEAGENT, $events)
                            || in_array(QueueLogEntity::COMPLETECALLER, $events)
                        )
                    ){
                        $correctSip = true;
                        $recs[$key] = [
                            'status' => ReportStatus::ANSWERED,
                            'callid' => $key,
                        ];
                        foreach ($datum as $item){
                            $fetch[] = $item['id'];

                            if($item['event'] == QueueLogEntity::ENTERQUEUE){
                                $recs[$key]['num'] = $item['data2'];
                            }
                            if($item['event'] == QueueLogEntity::CONNECT){

                                if($correctSip && isset(array_flip($reportIDs)[$item['agent']])){
                                    $recs[$key]['call_at'] = $item['time'];
                                    $recs[$key]['report_id'] = array_flip($reportIDs)[$item['agent']];
                                } else {
                                    $correctSip = false;
                                }
                            }
                            if(
                                $item['event'] == QueueLogEntity::COMPLETEAGENT
                                || $item['event'] == QueueLogEntity::COMPLETECALLER
                            ){
                                $recs[$key]['wait'] = $item['data1'];
                                $recs[$key]['total_time'] = $item['data2'];
                            }
                        }

                        if(!$correctSip){
                            $recs = [];
                            $fetch = [];
                            $ignore = array_column($datum, 'id');
                        }
                    } elseif (
                        // case 2
                        // звонок пришел (ENTERQUEUE), законектился с агентом (CONNECT) и
                        // агент, не отвечая его перенаправил (BLINDTRANSFER) на другого агента, из другого отдела
                        count($events) == 3
                        && in_array(QueueLogEntity::ENTERQUEUE, $events)
                        && in_array(QueueLogEntity::CONNECT, $events)
                        && in_array(QueueLogEntity::BLINDTRANSFER, $events)
                    ) {
                        $correctSip = true;
                        $recs[$key] = [
                            'status' => ReportStatus::TRANSFER,
                            'callid' => $key,
                        ];
                        foreach ($datum as $item){
                            $fetch[] = $item['id'];

                            if($item['event'] == QueueLogEntity::ENTERQUEUE){
                                $recs[$key]['num'] = $item['data2'];
                            }
                            if($item['event'] == QueueLogEntity::CONNECT){
                                if($correctSip && isset(array_flip($reportIDs)[$item['agent']])){
                                    $recs[$key]['call_at'] = $item['time'];
                                    $recs[$key]['report_id'] = array_flip($reportIDs)[$item['agent']];
                                } else {
                                    $correctSip = false;
                                }
                            }
                            if(
                                $item['event'] == QueueLogEntity::BLINDTRANSFER
                            ){
                                $recs[$key]['wait'] = $item['data3'];
                                $recs[$key]['total_time'] = $item['data4'];
                            }
                        }

                        if(!$correctSip){
                            $recs = [];
                            $fetch = [];
                            $ignore = array_column($datum, 'id');
                        }
                    } elseif (
                        // case 4
                        // звонок пришел (ENTERQUEUE), агент не ответил(RINGNOANSWER)(создаем запись что не ответил если звонок бал больше секунды), и
                        // звонок распределился на другого агента (CONNECT), и он ответил
                        count($events) == 4
                        && in_array(QueueLogEntity::ENTERQUEUE, $events)
                        && in_array(QueueLogEntity::RINGNOANSWER, $events)
                        && in_array(QueueLogEntity::CONNECT, $events)
                        && (
                            in_array(QueueLogEntity::COMPLETECALLER, $events)
                            || in_array(QueueLogEntity::COMPLETEAGENT, $events)
                        )
                    ) {
                        $correctSip = true;
                        $fromNum = '';
                        $noAnswer = [];
                        $complete = [];
                        foreach ($datum as $item){
                            $fetch[] = $item['id'];
                            if($item['event'] == QueueLogEntity::ENTERQUEUE) {
                                if(!$fromNum){
                                    $fromNum = $item['data2'];
                                }
                            }
                            if($item['event'] == QueueLogEntity::RINGNOANSWER){
                                if($item['data1'] >= self::RING_NO_ANSWER_MILLISECONDS){
                                    $noAnswer[] = [
                                        'agent' => $item['agent'],
                                        'wait' => $item['data1'],
                                        'time' => $item['time']
                                    ];
                                }
                            }
                            if(
                                $item['event'] == QueueLogEntity::COMPLETEAGENT
                                || $item['event'] == QueueLogEntity::COMPLETECALLER
                            ){
                                $complete[] = [
                                    'agent' => $item['agent'],
                                    'wait' => $item['data1'],
                                    'time' => $item['time'],
                                    'total_time' => $item['data2']
                                ];
                            }
                        }

                        foreach ($noAnswer as $n){
                            if($correctSip && isset(array_flip($reportIDs)[$n['agent']])){
                                $recs[] = [
                                    'status' => ReportStatus::NO_ANSWER,
                                    'callid' => $key,
                                    'num' => $fromNum,
                                    'report_id' => array_flip($reportIDs)[$n['agent']],
                                    'wait' => convertMillisecondToSecond($n['wait']),
                                    'call_at' => $n['time'],
                                ];
                            } else {
                                $correctSip = false;
                            }
                        }
                        foreach ($complete as $c){
                            if($correctSip && isset(array_flip($reportIDs)[$c['agent']])){
                                $recs[] = [
                                    'status' => ReportStatus::ANSWERED,
                                    'callid' => $key,
                                    'num' => $fromNum,
                                    'report_id' => array_flip($reportIDs)[$c['agent']],
                                    'wait' => $c['wait'],
                                    'call_at' => $c['time'],
                                    'total_time' => $c['total_time'],
                                ];
                            } else {
                                $correctSip = false;
                            }
                        }

                    if(!$correctSip){
                        $recs = [];
                        $fetch = [];
                        $ignore = array_column($datum, 'id');
                    }
                    } elseif (
                        // case 5
                        in_array(QueueLogEntity::ENTERQUEUE, $events)
                        && (
                            in_array(QueueLogEntity::RINGNOANSWER, $events)
                            || in_array(QueueLogEntity::RINGCANCELED, $events)
                        )
                        && in_array(QueueLogEntity::ABANDON, $events)
                    ) {
                        $correctSip = true;
                        $fromNum = '';
                        $noAnswer = [];
                        foreach ($datum as $item){
                            $fetch[] = $item['id'];
                            if($item['event'] == QueueLogEntity::ENTERQUEUE) {
                                if(!$fromNum){
                                    $fromNum = $item['data2'];
                                }
                            }
                            if(
                                $item['event'] == QueueLogEntity::RINGNOANSWER
                                || $item['event'] == QueueLogEntity::RINGCANCELED
                            ){
                                if($item['data1'] >= self::RING_NO_ANSWER_MILLISECONDS){
                                    $noAnswer[] = [
                                        'agent' => $item['agent'],
                                        'wait' => $item['data1'],
                                        'time' => $item['time']
                                    ];
                                }
                            }
                        }

                        foreach ($noAnswer as $n){
                            if($correctSip && isset(array_flip($reportIDs)[$n['agent']])){
                                $recs[] = [
                                    'status' => ReportStatus::NO_ANSWER,
                                    'callid' => $key,
                                    'num' => $fromNum,
                                    'report_id' => array_flip($reportIDs)[$n['agent']],
                                    'wait' => convertMillisecondToSecond($n['wait']),
                                    'call_at' => $n['time'],
                                ];
                            } else {
                                $correctSip = false;
                            }
                        }

                        if(!$correctSip){
                            $recs = [];
                            $fetch = [];
                            $ignore = array_column($datum, 'id');
                        }
                    } elseif(
                        // case 6
                        in_array(QueueLogEntity::ENTERQUEUE, $events)
                        && in_array(QueueLogEntity::CONNECT, $events)
                        && in_array(QueueLogEntity::BLINDTRANSFER, $events)
                        && (
                            in_array(QueueLogEntity::COMPLETEAGENT, $events)
                            || in_array(QueueLogEntity::COMPLETECALLER, $events)
                        )
                        || in_array(QueueLogEntity::RINGNOANSWER, $events)
                    ) {
                        $correctSip = true;
                        $fromNum = '';
                        $transfer = [];
                        $noAnswer = [];
                        $complete = [];
                        foreach ($datum as $item){
                            $fetch[] = $item['id'];
                            if($item['event'] == QueueLogEntity::ENTERQUEUE) {
                                if(!$fromNum){
                                    $fromNum = $item['data2'];
                                }
                            }
                            if($item['event'] == QueueLogEntity::BLINDTRANSFER){
                                $transfer[] = [
                                    'agent' => $item['agent'],
                                    'wait' => $item['data3'],
                                    'total_time' => $item['data4'],
                                    'time' => $item['time']
                                ];
                            }
                            if($item['event'] == QueueLogEntity::RINGNOANSWER){
                                if($item['data1'] >= self::RING_NO_ANSWER_MILLISECONDS){
                                    $noAnswer[] = [
                                        'agent' => $item['agent'],
                                        'wait' => $item['data1'],
                                        'time' => $item['time']
                                    ];
                                }
                            }
                            if(
                                $item['event'] == QueueLogEntity::COMPLETEAGENT
                                || $item['event'] == QueueLogEntity::COMPLETECALLER
                            ){
                                $complete[] = [
                                    'agent' => $item['agent'],
                                    'wait' => $item['data1'],
                                    'time' => $item['time'],
                                    'total_time' => $item['data2']
                                ];
                            }
                        }

                        foreach ($transfer as $t){
                            if($correctSip && isset(array_flip($reportIDs)[$t['agent']])){
                                $recs[] = [
                                    'status' => ReportStatus::TRANSFER,
                                    'callid' => $key,
                                    'num' => $fromNum,
                                    'report_id' => array_flip($reportIDs)[$t['agent']],
                                    'wait' => $t['wait'],
                                    'total_time' => $t['total_time'],
                                    'call_at' => $t['time'],
                                ];
                            } else {
                                $correctSip = false;
                            }
                        }
                        foreach ($noAnswer as $n){
                            if($correctSip && isset(array_flip($reportIDs)[$n['agent']])){
                                $recs[] = [
                                    'status' => ReportStatus::NO_ANSWER,
                                    'callid' => $key,
                                    'num' => $fromNum,
                                    'report_id' => array_flip($reportIDs)[$n['agent']],
                                    'wait' => convertMillisecondToSecond($n['wait']),
                                    'call_at' => $n['time'],
                                ];
                            } else {
                                $correctSip = false;
                            }
                        }
                        foreach ($complete as $c){
                            if($correctSip && isset(array_flip($reportIDs)[$c['agent']])){
                                $recs[] = [
                                    'status' => ReportStatus::ANSWERED,
                                    'callid' => $key,
                                    'num' => $fromNum,
                                    'report_id' => array_flip($reportIDs)[$c['agent']],
                                    'wait' => $c['wait'],
                                    'call_at' => $c['time'],
                                    'total_time' => $c['total_time'],
                                ];
                            } else {
                                $correctSip = false;
                            }
                        }

                        if(!$correctSip){
                            $recs = [];
                            $fetch = [];
                            $ignore = array_column($datum, 'id');
                        }
                    } else {
                        foreach ($datum as $item){
                            $ignore[] = $item['id'];
                        }
                    }
                }
                // если данного сипа у нас нету, то игнорим запись
                // todo подумать над лучшим вариантом
//                if(!$correctSip){
//                    $recs = [];
//                    $fetch = [];
//                    $ignore = array_column($datum, 'id');
//                }

                foreach ($recs as $rec){
                    $this->reportItemService->create(
                        ReportItemDto::byArgs($rec)
                    );
                }
                $countUpload += count($recs);

                if(!empty($fetch)){
                    $countFetch += count($fetch);
                    $this->updateIsFetch($fetch);
                }

                if(!empty($ignore)){
                    $countIgnore += count($ignore);
                    $this->updateIsIgnore($ignore);
                }
            }

            logger_info("SYNC Queue Log - upload [{$countUpload}] records, fetch - [{$countFetch}], ignore - [{$countIgnore}]");
        } catch (\Exception $e){
            logger_info("SYNC Queue Log - {$e->getMessage()}");
            throw new \Exception($e);
        }
    }

    public function uploadPauseData()
    {
        try {
            $countUpload = 0;
            $countFetch = 0;

            $departmentsIDs = $this->departmentRepository->getDepartmentIdsAndName();

            $reportIDs = $this->reportRepository->getReportIdAdnSipNumber();

            foreach ($departmentsIDs as $name => $id){
                $data = [];
                $recs = [];
                $recsTmp = [];
                $fetch = [];

                $this->getConectionDb()
                    ->where('queuename', $name)
                    ->whereIn('event', QueueLogEntity::eventForPause())
                    ->where(function (Builder $q){
                        return $q->whereNotIn('is_fetch', [1, 2])
                            ->orWhereNull('is_fetch');
                    })
                    ->orderBy('time')
                    ->get()
                    ->each(function ($item) use (&$data) {
                        $item = stdToArray($item);
                        $data[$item['agent']][] = $item;
                    });

                foreach ($data as $item){
                    // отсекаем последние эл. если они pause, но в бд asterisk не проставляем их как - вытянутые,
                    // это кейс когда сотрудник вошел в паузу, но на момент обработки данных из нее не вышел
                    // данные будут обработаны, со следующим запуском обработчика, если у него появиться - unpause
                    $this->deleteDataIfLastPause($item);


                    // отсеиваем данные, если подряд идут несколько pause/unpause,
                    // берем первый эл. все остальные - отсекаем
                    // в итоге получаем данные где последовательно идут - pause,unpause,pause,unpause, ....
                    $keyPause = true;
                    $keyUnpause = true;
                    $itemTmp = [];
                    foreach ($item as $k => $i) {
                        $fetch[] = $i['id'];

                        if($keyPause && $i['event'] == QueueLogEntity::PAUSE){
                            $keyPause = false;
                            $keyUnpause = true;
                            $itemTmp[$k] = $i;
                        }
                        if($keyUnpause && $i['event'] == QueueLogEntity::UNPAUSE){
                            $keyPause = true;
                            $keyUnpause = false;
                            $itemTmp[$k] = $i;
                        }
                    }

                    $recsTmp = array_values($itemTmp);
                }

                // формируем данные для записи
                foreach ($recsTmp ?? [] as $key => $rec){
                    if($rec['event'] == QueueLogEntity::PAUSE){
                        $recs[$key]['pause_data'] = $rec;
                        $recs[$key]['unpause_data'] = $recsTmp[$key + 1];
                        $recs[$key]['report_id'] = array_flip($reportIDs)[$rec['agent']];
                        $recs[$key]['report_id'] = array_flip($reportIDs)[$rec['agent']];
                    }
                }

                $countUpload += count($recs);
                $this->reportPauseItemService->insert($recs);

                // записям в бд asterisk проставляем что он обработаны
                if(!empty($fetch)){
                    $countFetch += count($fetch);
                    $this->updateIsFetch($fetch);
                }
            }

            logger_info("SYNC Pause - upload [{$countUpload}] records, fetch - [{$countFetch}]");
        } catch (\Exception $e){
            logger_info("SYNC Pause - {$e->getMessage()}");
            throw new \Exception($e);
        }
    }

    private function deleteDataIfLastPause(array &$data): void
    {
        if(!empty($data) && last($data)['event'] == QueueLogEntity::PAUSE){
            unset($data[array_key_last($data)]);
            self::deleteDataIfLastPause($data);
        }
    }

    public function updateIsFetch(array $ids)
    {
        return $this->getConectionDb()
            ->whereIn('id', $ids)
            ->update(['is_fetch' => 1]);
    }

    public function updateIsIgnore(array $ids)
    {
        return $this->getConectionDb()
            ->whereIn('id', $ids)
            ->update(['is_fetch' => 2]);
    }

    public function create(array $data)
    {
        $this->insert($data);
    }
}

