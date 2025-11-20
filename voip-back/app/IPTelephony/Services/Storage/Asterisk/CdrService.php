<?php

namespace App\IPTelephony\Services\Storage\Asterisk;

use App\Dto\Calls\HistoryDto;
use App\Enums\Calls\HistoryStatus;
use App\Enums\Calls\QueueStatus;
use App\IPTelephony\Entities\Asterisk\CdrEntity;
use App\Models\Employees\Employee;
use App\Repositories\Departments\DepartmentRepository;
use App\Repositories\Employees\EmployeeRepository;
use App\Repositories\Sips\SipRepository;
use App\Services\Calls\HistoryService;
use Illuminate\Database\Query\Builder;
use App\Services\Calls\QueueService as AppQueueService;
use Illuminate\Support\Facades\DB;

class CdrService extends AsteriskService
{
    public function __construct(
        protected SipRepository $sipRepository,
        protected EmployeeRepository $employeeRepository,
        protected DepartmentRepository $departmentRepository,
        protected HistoryService $historyService,
        protected AppQueueService $queueService,
    )
    {}

    public function getTable(): string
    {
        return CdrEntity::TABLE;
    }

    public function uploadCdrData()
    {
//        try {
            $employeeIDs = $this->employeeRepository->getEmployeeIdsAndUuid();
            $departmentsIDs = $this->departmentRepository->getDepartmentIdsAndUuid();

            $data = [];
            // Dial - звонок между агентами, Queue - звонок на очередь, Hangup - звонок завершен
            // вытягиваем новые данные по истории, т.к. у нас могут по одному uniqueid может быть несколько записей
            // формируем многомерный массив данных, где ключом будет uniqueid, а значениями его данные по этому uniqueid,
            // их может быть как один так и несколько
            $this->getConectionDb()
                ->whereIn('lastapp', CdrEntity::fetchingType())
                ->whereIn('department_uuid', array_flip($departmentsIDs))
                ->where(function (Builder $q){
                    return $q->where('is_fetch', '!=', 1)
                        ->where('is_fetch', '!=', 2)
                        ->orWhereNull('is_fetch');
                })
                ->orderBy('calldate')
                ->get()
                ->each(function ($item) use (&$data) {
                    $item = stdToArray($item);
                    $data[$item['uniqueid']][] = $item;
                });

            foreach ($data as $uniqueId => $datum){

                try {
                    $this->handlerHistoryCases($datum, $employeeIDs, $departmentsIDs);

                    logger_info("SYNC CDR - handler [{$uniqueId}]");
                } catch (\Exception $e){
                    $this->updateIsFail($uniqueId);
                    logger_info("SYNC CDR FAIL - [{$uniqueId}]");
                }
            }

            $this->uploadBackgroundData();

//            logger_info("SYNC CDR - upload [{$countUpload}] records, fetch [{$countFetch}] records");
//        } catch (\Exception $e){
//            logger_info("SYNC CDR FAIL - {$e->getMessage()}");
//            throw new \Exception($e);
//        }
    }

    private function handlerHistoryCases(array $datum, array $employeeIDs, array $departmentsIDs)
    {
//        dd($datum);
        $recs = [];
        // получаем какие значения есть в полях - lastapp (тип соединения - Queue, Dial, Hangup), для конкретного - uniqueid
        $valuesLastapp = array_flip(array_diff(array_column($datum, 'lastapp'), ['', NULL, false]));
        // получаем какие значения есть в полях - true_reason_hangup, для конкретного - uniqueid, чтоб узнать есть ли трансфер звонка
        $valuesReasonHangup = array_flip(array_diff(array_column($datum, 'true_reason_hangup'), ['', NULL, false, 'NULL']));
        // получаем какие значения есть в полях - disposition(статус), для конкретного - uniqueid
        $valuesDisposition = array_flip(array_diff(array_column($datum, 'disposition'), ['', NULL, false]));

        // case 1, 2, 3, 15
        // записей больше двух, есть трансфер и нет перенаправления на очередь
        // звонок пришел на агента, он его перенаправил(трансфер) на другого агента
        // может быть hangup (инициатор звонка положил трубку первый) или не быть
        if(
            count($datum) > 1
            && array_key_exists(CdrEntity::STATUS_TRANSFER, $valuesReasonHangup)
            && !array_key_exists(CdrEntity::TYPE_QUEUE, $valuesLastapp)
            && array_key_exists(CdrEntity::TYPE_DIAL, $valuesLastapp)
            && !array_key_exists(CdrEntity::STATUS_CRM_TRANSFER, $valuesReasonHangup)
        ){
            //////////////////////////////////
            $reason = array_count_values(array_diff(array_column($datum, 'true_reason_hangup'), ['', NULL, false]));

            if($reason[CdrEntity::STATUS_TRANSFER] >= 2){
                // case 3
                foreach ($datum as $k => $item){
                    // пропускаем Hangup, если есть, и он нам не нужен, для записи в историю
                    if($item['lastapp'] != CdrEntity::TYPE_HANGUP){
                        $recs[] = array_merge($item, [
                            'dst' => self::parseNumFromChanel($item['lastdata']),
                            'disposition' => array_key_exists($k + 1, $datum)
                                ? $datum[$k + 1]['true_reason_hangup']
                                : $item['disposition'],
                            'employee_id' => $employeeIDs[$item['employee_uuid']] ?? null,
                            'from_employee_id' => $employeeIDs[$item['from_employee_uuid']] ?? null,
                            'department_id' => $departmentsIDs[$item['department_uuid']] ?? null
                        ]);
                    }
                }

                logger_info("CDR UPLOAD implement case_3 [{$datum[0]['uniqueid']}]");
            } else {
                foreach ($datum as $k => $item){
                    // пропускаем Hangup, если есть, и он нам не нужен, для записи в историю
                    if($item['lastapp'] != CdrEntity::TYPE_HANGUP){
                        $recs[] = array_merge($item, [
                            'dst' => $k == array_key_first($datum)
                                ? self::parseNumFromChanel($item['lastdata'])
                                : $item['dst'],
                            'disposition' => array_key_exists($k + 1, $datum)
                                ? $datum[$k + 1]['true_reason_hangup']
                                : $item['disposition'],
                            'employee_id' => $employeeIDs[$item['employee_uuid']] ?? null,
                            'department_id' => $departmentsIDs[$item['department_uuid']] ?? null,
                            'from_employee_id' => $employeeIDs[$item['from_employee_uuid']] ?? null,
                        ]);
                    }
                }

                logger_info("CDR UPLOAD implement case_1/2/15 [{$datum[0]['uniqueid']}]");
            }

        } elseif (
            // записей больше двух, есть трансфер и есть перенаправление на очередь
            // звонок был на агента, потом перенаправлен(трансфер) в очередь, там и закончился
            count($datum) > 1
            && count($valuesLastapp) > 1
            && array_key_first($valuesLastapp) == CdrEntity::TYPE_DIAL
            && array_key_exists(CdrEntity::STATUS_TRANSFER, $valuesReasonHangup)
            && array_key_exists(CdrEntity::TYPE_QUEUE, $valuesLastapp)
        ){
            ///////////////////////////////////
            $duration = 0;
            $billsec = 0;
            $dst = null;
            $disposition = null;
            foreach ($datum as $k => $item){
                if($item['lastapp'] == CdrEntity::TYPE_DIAL){
                    $recs[] = array_merge($item, [
                        'dst' => self::parseNumFromChanel($item['lastdata']),
                        'disposition' => HistoryStatus::TRANSFER,
                        'employee_id' => $employeeIDs[$item['employee_uuid']] ?? null,
                        'department_id' => $departmentsIDs[$item['department_uuid']] ?? null,
                        'from_employee_id' => $employeeIDs[$item['from_employee_uuid']] ?? null,
                    ]);
                } elseif ($item['lastapp'] == CdrEntity::TYPE_QUEUE) {
                    $duration += $item['duration'];
                    $billsec += $item['billsec'];
                    if(!$dst && $k != array_key_last($datum)){
                        $dst = self::parseNumFromChanel($item['dstchannel']);
                    }
                    if($k == array_key_last($datum)){
                        $disposition = $item['true_reason_hangup'] == CdrEntity::STATUS_ANSWER
                            ? HistoryStatus::ANSWERED
                            : $item['true_reason_hangup'];
                    }
                }
            }

            $recs[] = array_merge($datum[array_key_last($datum)], [
                'dst' => $dst,
                'duration' => $duration,
                'billsec' => $billsec,
                'disposition' => $disposition,
                'employee_id' => $employeeIDs[$datum[array_key_last($datum)]['employee_uuid']] ?? null,
                'department_id' => $departmentsIDs[$datum[array_key_last($datum)]['department_uuid']] ?? null,
                'from_employee_id' => $employeeIDs[$item['from_employee_uuid']] ?? null,
            ]);

        } elseif (
            // case 7
            // звонок пришел в очередь, потом перенаправлен на агента, там и закончился
            count($datum) > 1
            && count($valuesLastapp) > 1
            && array_key_first($valuesLastapp) == CdrEntity::TYPE_QUEUE
            && array_key_exists(CdrEntity::STATUS_TRANSFER, $valuesReasonHangup)
            && !array_key_exists(CdrEntity::STATUS_CRM_TRANSFER, $valuesReasonHangup)
            && array_key_exists(CdrEntity::TYPE_QUEUE, $valuesLastapp)
            && array_key_exists(CdrEntity::TYPE_DIAL, $valuesLastapp)
        ) {
            //////////////////////////////////////////
            $duration = 0;
            $billsec = 0;
            $dst = null;
            $src = null;
            $disposition = null;
            foreach ($datum as $k => $item){
                if($item['lastapp'] == CdrEntity::TYPE_QUEUE){
                    $duration += $item['duration'];
                    $billsec += $item['billsec'];
                    $disposition =  HistoryStatus::TRANSFER;
                    if(!$dst && $item['disposition'] == CdrEntity::STATUS_ANSWERED){
                        $dst = self::parseNumFromChanel($item['dstchannel']);
                    }
                    if(!$src){
                        $src = $item['src'];
                    }
                } elseif ($item['lastapp'] == CdrEntity::TYPE_DIAL) {

                    $status = $datum[array_key_last($datum)]['lastapp'] == CdrEntity::TYPE_HANGUP
                        ? $datum[array_key_last($datum)]['true_reason_hangup']
                        : $datum[array_key_last($datum)]['disposition']
                    ;

                    $recs[$k] = array_merge($item, [
                        'src' => $src,
                        'dst' => self::parseNumFromChanel($item['lastdata']),
                        'disposition' => $status,
                        'employee_id' => $employeeIDs[$item['employee_uuid']] ?? null,
                        'department_id' => $departmentsIDs[$item['department_uuid']] ?? null
                    ]);
                }
            }

            $recs[0] = array_merge($datum[0], [
                'dst' => $dst,
                'src' => $src,
                'duration' => $duration,
                'billsec' => $billsec,
                'disposition' => $disposition,
                'employee_id' => $employeeIDs[$datum[0]['employee_uuid']] ?? null,
                'department_id' => $departmentsIDs[$datum[0]['department_uuid']] ?? null
            ]);

            sort($recs);

            // доработка для кейса 7.4 (если записей больше двух, проставляем всем , кроме последней, статус трансфер)
            if(count($recs) > 2){
                foreach ($recs as $k => $rec){
                    if($k != array_key_last($recs)){
                        $recs[$k]['disposition'] = CdrEntity::STATUS_TRANSFER;
                    }
                }
            }

            logger_info("CDR UPLOAD implement case_7 [{$datum[0]['uniqueid']}]");
        } elseif(
            // case 24
            count($datum) > 1
            && count($valuesLastapp) > 1
            && array_key_exists(CdrEntity::STATUS_CRM_TRANSFER, $valuesReasonHangup)
            && array_key_exists(CdrEntity::TYPE_QUEUE, $valuesLastapp)
            && array_key_exists(CdrEntity::TYPE_DIAL, $valuesLastapp)
        ){
            ///////////////////////////////////
            foreach ($datum as $item){
                if($item['true_reason_hangup'] === CdrEntity::STATUS_CRM_TRANSFER){

                    $dialedName = null;
                    if($item['from_employee_uuid']){
                        $fromEmployee = DB::table(Employee::TABLE)
                            ->select(['first_name', 'last_name'])
                            ->where('guid', $item['from_employee_uuid'])
                            ->first();

                        $dialedName = $fromEmployee->first_name .' '. $fromEmployee->last_name;
                    }

                    $recs[0] = array_merge($item, [
                        'dst' => $item['true_src'],
                        'dialed_name' => $dialedName,
                        'disposition' => CdrEntity::STATUS_CRM_TRANSFER,
                        'employee_id' => $employeeIDs[$item['employee_uuid']] ?? null,
                        'department_id' => $departmentsIDs[$item['department_uuid']] ?? null,
                        'from_employee_id' => $employeeIDs[$item['from_employee_uuid']] ?? null
                    ]);
                    $recs[1] = array_merge($item, [
                        'dst' => $item['dst'],
                        'disposition' => $item['disposition'],
                        'employee_id' => $employeeIDs[$item['employee_uuid']] ?? null,
                        'department_id' => $departmentsIDs[$item['department_uuid']] ?? null
                    ]);
                }
            }

            logger_info("CDR UPLOAD implement case_24 [{$datum[0]['uniqueid']}]");
        } elseif(
            count($datum) > 1
            && count($valuesLastapp) == 1
            && array_key_first($valuesLastapp) == CdrEntity::TYPE_QUEUE
            && array_key_exists(CdrEntity::STATUS_TRANSFER, $valuesReasonHangup)
        ){
            //////////////////////////////////////
            $valuesTrueSrc = array_flip(array_diff(array_column($datum, 'true_src'), ['', NULL, false]));

            if(array_key_exists(CdrEntity::STATUS_CRM_TRANSFER, $valuesReasonHangup)){
                // case 19
                // получаем массив гуид агента и его сип, чтоб связать записи
                $employeeUuidAndSip = $this->employeeRepository->getEmployeeGuidAdnSipNumber();

                $copyDatum = [];
                // схлопываем дублирующую запись по дате
                foreach ($datum as $d){
                    $copyDatum[$d['calldate']] = $d;
                }
                // создаем первую запись с crm_transfer
                foreach ($copyDatum as $item){
                    if($item['true_reason_hangup'] === CdrEntity::STATUS_CRM_TRANSFER){
                        $recs[] = array_merge($item, [
                            'dst' => $item['true_src'],
                            'disposition' => HistoryStatus::CRM_TRANSFER,
                            'department_id' => $departmentsIDs[$item['department_uuid']] ?? null
                        ]);
                    }
                }

                // формируем остальные записи
                foreach ($copyDatum as $item){
                    if($item['employee_uuid']){
                        $duration = $item['duration'];
                        $billsec = $item['billsec'];
                        $dst = null;
                        foreach ($copyDatum as $i){
                            if(self::parseNumFromChanel($i['dstchannel']) == $employeeUuidAndSip[$item['employee_uuid']]){
                                $duration += $i['duration'];
                                $billsec += $i['billsec'];
                                $dst = self::parseNumFromChanel($i['dstchannel']);
                            }
                        }

                        $recs[] = array_merge($item, [
                            'dst' => $dst,
                            'duration' => $duration,
                            'billsec' => $billsec,
                            'employee_id' => $employeeIDs[$item['employee_uuid']] ?? null,
                            'department_id' => $departmentsIDs[$item['department_uuid']] ?? null
                        ]);
                    }
                }

                // проставляем статус трансфер
                foreach ($recs as $key => $rec){
                    if($rec['disposition'] !== HistoryStatus::CRM_TRANSFER && $key !== array_key_last($recs)){
                        $recs[$key]['disposition'] = HistoryStatus::TRANSFER;
                    }
                }

                logger_info("CDR UPLOAD implement case_19 [{$datum[0]['uniqueid']}]");
            } else {
                // обрабатываем только если в true_src есть данные, иначе это другой кейс
                if(count($valuesTrueSrc) > 0){
                    // case 10
                    // клиент позвонил в очередь, там ответили и перенаправили в другую очередь
                    $src = null;
                    $createdRec = false;
                    if($datum[0]['true_reason_hangup'] == CdrEntity::STATUS_TRANSFER){
                        $createdRec = true;
                    }
                    $copyDatum = [];
                    foreach ($datum as $d){
                        if(!$src){
                            $src = $d['src'];
                        }
                        $d['dstchannel'] = stristr($d['dstchannel'], ';', true);
                        $d['true_src'] = stristr($d['true_src'], ';', true);

                        $copyDatum[$d['calldate']] = $d;
                    }

                    $recs = [];

                    if($createdRec){

                        $key = 0;
                        $duration = 0;
                        $billsec = 0;
                        foreach ($copyDatum as $k => $item){
                            $duration += $item['duration'];
                            $billsec += $item['billsec'];
                            if($item['employee_uuid'] && $item['true_src']){
                                $key = $k;
                            }
                        }

                        $recs[] = array_merge($copyDatum[$key], [
                            'dst' => self::parseNumFromChanel($copyDatum[$key]['true_src']),
                            'duration' => $duration,
                            'billsec' => $billsec,
                            'disposition' => CdrEntity::STATUS_ANSWER,
                            'department_id' => $departmentsIDs[$copyDatum[$key]['department_uuid']] ?? null,
                            'employee_id' => $employeeIDs[$copyDatum[$key]['employee_uuid']] ?? null,
                        ]);

                        logger_info("CDR UPLOAD implement case_10.5 [{$datum[0]['uniqueid']}]");
                    } else {
                        foreach ($copyDatum as $k => $item) {
                            if($item['employee_uuid']){
                                $duration = 0;
                                $billsec = 0;
                                if($item['true_src']){
                                    $dst = self::parseNumFromChanel($item['true_src']);
                                    $duration += $item['duration'];
                                    $billsec += $item['billsec'];
                                    foreach ($copyDatum as $d){
                                        if($d['dstchannel'] == $item['true_src']){
                                            $duration += $d['duration'];
                                            $billsec += $d['billsec'];
                                        }
                                    }

                                } else {
                                    $dst = self::parseNumFromChanel($item['true_src']);
                                    $duration += $item['duration'];
                                    $billsec += $item['billsec'];
                                }

                                $recs[] = array_merge($item, [
                                    'dst' => $dst,
                                    'duration' => $duration,
                                    'billsec' => $billsec,
                                    'department_id' => $departmentsIDs[$item['department_uuid']] ?? null,
                                    'employee_id' => $employeeIDs[$item['employee_uuid']],
                                ]);
                            }

                        }

                        if(count($recs) == 1){
                            $lastItem = $copyDatum[array_key_last($copyDatum)];
                            $dst = null;
                            if($lastItem['dstchannel']){
                                $dst = self::parseNumFromChanel($item['dstchannel']);
                            } else {
                                $dst = '';

                            }
                            $recs[] = array_merge($lastItem, [
                                'dst' => $dst,
                                'disposition' => CdrEntity::STATUS_NO_ANSWER,
                                'department_id' => $departmentsIDs[$lastItem['department_uuid']] ?? null,
                                'employee_id' => $employeeIDs[$lastItem['employee_uuid']] ?? null,
                            ]);
                        }

                        foreach ($recs as $k => $rec){
                            if($k != array_key_last($recs)){
                                $recs[$k]['disposition'] = CdrEntity::STATUS_TRANSFER;
                            }
                        }

                        logger_info("CDR UPLOAD implement case_10 [{$datum[0]['uniqueid']}]");
                    }
                }
            }

        } elseif (
            // звонок пришел в очередь, там и закончился
            count($datum) > 1
            && empty($valuesReasonHangup)
            && count($valuesLastapp) == 1
            && array_key_exists(CdrEntity::TYPE_QUEUE, $valuesLastapp)
            && array_key_exists(CdrEntity::STATUS_ANSWERED, $valuesDisposition)
        ){
            ///////////////////////////////////////
            $duration = 0;
            $billsec = 0;
            $dst = null;
            foreach ($datum as $item){
                $duration += $item['duration'];
                $billsec += $item['billsec'];
                if(!$dst && $item['disposition'] != CdrEntity::STATUS_NO_ANSWER){
                    $dst = self::parseNumFromChanel($item['dstchannel']);
                }
            }

            $recs[] = array_merge( $datum[0], [
                'dst' => $dst,
                'duration' => $duration,
                'billsec' => $billsec,
                'disposition' => HistoryStatus::ANSWERED,
                'employee_id' => $employeeIDs[$datum[array_key_last($datum)]['employee_uuid']] ?? null,
                'department_id' => $departmentsIDs[$datum[array_key_last($datum)]['department_uuid']] ?? null
            ]);

        } elseif (
            // case 9
            // звонок пришел в очередь, там и закончился, и не был отвечен
            count($datum) > 1
            && count($valuesLastapp) >= 1
//                    && (array_key_exists(CdrEntity::TYPE_QUEUE, $valuesLastapp) || array_key_exists(CdrEntity::TYPE_HANGUP, $valuesLastapp))
            && (array_key_exists(CdrEntity::TYPE_QUEUE, $valuesLastapp))
            && array_key_exists('NO ANSWER', $valuesDisposition)
            && !array_key_exists(CdrEntity::STATUS_CRM_TRANSFER, $valuesReasonHangup)
        ) {
            /////////////////////////////////////////////////
            $duration = 0;
            $billsec = 0;
            $dst = null;
            foreach ($datum as $item){
                $duration += $item['duration'];
                $billsec += $item['billsec'];
                if(!$dst){
                    $dst = self::parseNumFromChanel($item['dstchannel']);
                }
            }

            $recs[] = array_merge($datum[0], [
                'dst' => $dst,
                'duration' => $duration,
                'billsec' => $billsec,
                'disposition' => HistoryStatus::NO_ANSWER(),
                'employee_id' => $employeeIDs[$datum[array_key_last($datum)]['employee_uuid']] ?? null,
                'department_id' => $departmentsIDs[$datum[array_key_last($datum)]['department_uuid']] ?? null
            ]);
            logger_info("CDR UPLOAD implement case_9 [{$datum[0]['uniqueid']}]");
        } elseif (
            // case 12 or 21
            count($datum) >= 1
            && array_key_exists(CdrEntity::STATUS_CRM_TRANSFER, $valuesReasonHangup)
            && array_key_exists(CdrEntity::TYPE_QUEUE, $valuesLastapp)
            && !array_key_exists(CdrEntity::TYPE_DIAL, $valuesLastapp)
        ){
            /////////////////////////////////////////
            if(
                // case 21.1
                count($datum) == 1
                && array_key_exists(CdrEntity::STATUS_CRM_TRANSFER, $valuesReasonHangup)
            ) {
                $recs[0] = array_merge($datum[0], [
                    'dst' => $datum[0]['true_src'],
                    'disposition' => HistoryStatus::CRM_TRANSFER,
                    'employee_id' => $employeeIDs[$datum[0]['employee_uuid']] ?? null,
                    'department_id' => $departmentsIDs[$datum[0]['department_uuid']] ?? null
                ]);
                $recs[1] = array_merge($datum[0], [
                    'dst' => '',
                    'disposition' => HistoryStatus::NO_ANSWER,
                    'employee_id' => $employeeIDs[$datum[0]['employee_uuid']] ?? null,
                    'department_id' => $departmentsIDs[$datum[0]['department_uuid']] ?? null
                ]);
                logger_info("CDR UPLOAD implement case_21.1 [{$datum[0]['uniqueid']}]");
            } elseif (
                // case 21.2
                empty($datum[$valuesReasonHangup['CRM_TRANSFER']]['dstchannel'])
            ){
                $recs[0] = array_merge($datum[$valuesReasonHangup['CRM_TRANSFER']], [
                    'dst' => $datum[$valuesReasonHangup['CRM_TRANSFER']]['true_src'],
                    'disposition' => HistoryStatus::CRM_TRANSFER,
                    'employee_id' => $employeeIDs[$datum[$valuesReasonHangup['CRM_TRANSFER']]['employee_uuid']] ?? null,
                    'department_id' => $departmentsIDs[$datum[$valuesReasonHangup['CRM_TRANSFER']]['department_uuid']] ?? null
                ]);

                $duration = 0;
                $billsec = 0;
                foreach ($datum as $k => $item){
                    if($item['true_reason_hangup'] != CdrEntity::STATUS_CRM_TRANSFER){
                        $duration += $item['duration'];
                        $billsec += $item['billsec'];
                    }
                }

                $recs[1] = array_merge($datum[0], [
                    'dst' => '',
                    'duration' => $duration,
                    'billsec' => $billsec,
                    'disposition' => HistoryStatus::NO_ANSWER,
                    'employee_id' => $employeeIDs[$datum[0]['employee_uuid']] ?? null,
                    'department_id' => $departmentsIDs[$datum[0]['department_uuid']] ?? null
                ]);
                logger_info("CDR UPLOAD implement case_21.2 [{$datum[0]['uniqueid']}]");
            } else {
                // case 12

                // формируем первую запись
                $durationFirst = $datum[$valuesReasonHangup['CRM_TRANSFER']]['duration'];
                $billsecFirst = $datum[$valuesReasonHangup['CRM_TRANSFER']]['billsec'];
                if($datum[$valuesReasonHangup['CRM_TRANSFER']]['disposition'] == CdrEntity::STATUS_ANSWERED){
                    foreach ($datum as $item){
                        if($item['true_reason_hangup'] != CdrEntity::STATUS_CRM_TRANSFER){
                            $durationFirst += $item['duration'];
                            $billsecFirst += $item['billsec'];
                        }
                    }
                }

                $recs[0] = array_merge($datum[$valuesReasonHangup['CRM_TRANSFER']], [
                    'dst' => $datum[$valuesReasonHangup['CRM_TRANSFER']]['true_src'],
                    'duration' => $durationFirst,
                    'billsec' => $billsecFirst,
                    'disposition' => HistoryStatus::CRM_TRANSFER,
                    'employee_id' => $employeeIDs[$datum[$valuesReasonHangup['CRM_TRANSFER']]['employee_uuid']] ?? null,
                    'department_id' => $departmentsIDs[$datum[$valuesReasonHangup['CRM_TRANSFER']]['department_uuid']] ?? null
                ]);

                // ищем dst для второй запись
                $dstSecond = null;
                foreach ($datum as $item){
                    if(!$dstSecond && $item['disposition'] == CdrEntity::STATUS_ANSWERED){
                        $dstSecond = self::parseNumFromChanel($item['dstchannel']);
                    }
                }
                if(!$dstSecond){
                    foreach ($datum as $item){
                        if($item['lastapp'] == CdrEntity::TYPE_QUEUE){
                            $dstSecond = self::parseNumFromChanel($item['dstchannel']);
                        }
                    }
                }
                // формируем duration для второй записи
                $durationSecond = 0;
                $billsecSecond = 0;
                foreach ($datum as $item){
                    if($item['disposition'] == CdrEntity::STATUS_ANSWERED){
                        $durationSecond += $item['duration'];
                        $billsecSecond += $item['billsec'];
                    }
                }
                if($durationSecond == 0){
                    foreach ($datum as $item){
                        if(
                            $item['disposition'] == CdrEntity::STATUS_NO_ANSWER
                            && $item['true_reason_hangup'] != CdrEntity::STATUS_CRM_TRANSFER
                        ){
                            $durationSecond += $item['duration'];
                            $billsecSecond += $item['billsec'];
                        }
                    }
                }

                $key = null;
                foreach ($datum as $k => $item){
                    if(
                        $item['true_reason_hangup'] != CdrEntity::STATUS_CRM_TRANSFER
                        && $item['employee_uuid']
                        && !$key
                    ){
                        $key = $k;
                    }
                }
                if($key === null){
                    $key = $valuesReasonHangup['CRM_TRANSFER'];
                }

                $dispositionSecond = CdrEntity::STATUS_ANSWERED;
                if(
                    count($valuesDisposition) == 1
                    && array_key_exists(CdrEntity::STATUS_NO_ANSWER, $valuesDisposition)
                ){
                    $dispositionSecond = CdrEntity::STATUS_NO_ANSWER;
                }
                // формируем вторую запись
                $recs[1] = array_merge($datum[$key], [
                    'dst' => $dstSecond,
                    'duration' => $durationSecond,
                    'billsec' => $billsecSecond,
                    'disposition' => $dispositionSecond,
                    'employee_id' => $employeeIDs[$datum[$valuesReasonHangup['CRM_TRANSFER']]['employee_uuid']] ?? null,
                    'department_id' => $departmentsIDs[$datum[$valuesReasonHangup['CRM_TRANSFER']]['department_uuid']] ?? null
                ]);

                // при данном случае, запись в queue зависает и не меняется, поэтому переводим ее в cancel
                $this->queueService->setStatusByField('channel', $datum[0]['channel'], QueueStatus::CANCEL());
                logger_info("CDR UPLOAD implement case_12 [{$datum[0]['uniqueid']}]");
            }


        } elseif (
            // case 14
            // звонок трансферят из админки на агента, без очереди
            count($datum) >= 1
            && array_key_exists(CdrEntity::STATUS_CRM_TRANSFER, $valuesReasonHangup)
            && array_key_exists(CdrEntity::TYPE_DIAL, $valuesLastapp)
            && !array_key_exists(CdrEntity::TYPE_QUEUE, $valuesLastapp)
//                    && array_key_exists(CdrEntity::TYPE_HANGUP, $valuesLastapp)
        ){
            ////////////////////////////////////////////////
            $datumCopy = $datum;
            if($datumCopy[array_key_last($datumCopy)]['lastapp'] === CdrEntity::TYPE_HANGUP){
                unset($datumCopy[array_key_last($datumCopy)]);
            }

            $statuses = [];
            $dst = [];
            $tmp = [];

            $src = null;
            foreach ($datumCopy as $k => $item){
                if(!$src){
                    $src = $item['src'];
                }
                $statuses[] = $item['true_reason_hangup'];
                if($k == array_key_last($datumCopy)){
                    $statuses[] = $item['disposition'];
                }

                $tmp[] = $item;
                if($k == array_key_first($datumCopy)){
                    $tmp[] = $item;
                    $dst[] = $item['true_src'];
                }

                $dst[] = self::parseNumFromChanel($item['lastdata']);
            }

            foreach ($statuses as $key => $status){
                $recs[] = array_merge($tmp[$key],[
                    'src' => $src,
                    'disposition' => $status,
                    'dst' => $dst[$key],
                    'employee_id' => $employeeIDs[$tmp[$key]['employee_uuid']] ?? null,
                    'department_id' => $departmentsIDs[$tmp[$key]['department_uuid']] ?? null
                ]);
            }
            foreach ($recs as $key => $rec){
                if($rec['dst'] == 'admin'){
                    $recs[$key]['employee_id'] = null;
                }
            }

            // при данном случае, запись в queue зависает и не меняется, поэтому переводим ее в cancel
            $this->queueService->setStatusByField('channel', $datum[0]['channel'], QueueStatus::CANCEL());
            logger_info("CDR UPLOAD implement case_14 [{$datum[0]['uniqueid']}]");
        } elseif (
            (
                count($datum) == 2
                && $datum[0]['lastapp'] == CdrEntity::TYPE_DIAL
                && $datum[1]['lastapp'] == CdrEntity::TYPE_HANGUP
            )
            || count($datum) == 1
        ) {
            //////////////////////////////////////////////////
            if(
                $datum[0]['lastapp'] == CdrEntity::TYPE_QUEUE
                && count($valuesReasonHangup) == 1
                && array_key_exists('NO ANSWER', $valuesReasonHangup)
            ){
                // case 11
                // клиент звонит в очередь, не распредиляется и ложит трубку
                $recs[0] = array_merge($datum[0], [
                    'dst' => self::parseNumFromChanel($datum[0]['dstchannel']) ?? $datum[0]['dst'],
                    'disposition' => HistoryStatus::NO_ANSWER,
                    'employee_id' => $employeeIDs[$datum[0]['employee_uuid']] ?? null,
                    'department_id' => $departmentsIDs[$datum[0]['department_uuid']] ?? null
                ]);

                // при данном случае, запись в queue зависает и не меняется, поэтому переводим ее в cancel
                $this->queueService->setStatusByField('channel', $datum[0]['channel'], QueueStatus::CANCEL());
                logger_info("CDR UPLOAD implement case_11 [{$datum[0]['uniqueid']}]");
            } elseif (
                count($datum) == 1
                && $datum[0]['lastapp'] == CdrEntity::TYPE_HANGUP
            ){
                // case 13
                // клиент звонит в очередь, и вылетает по таймауту
                $recs[0] = array_merge($datum[0], [
                    'disposition' => $datum[0]['true_reason_hangup'],
                    'employee_id' => $employeeIDs[$datum[0]['employee_uuid']] ?? null,
                    'department_id' => $departmentsIDs[$datum[0]['department_uuid']] ?? null
                ]);

                // при данном случае, запись в queue зависает и не меняется, поэтому переводим ее в cancel
                $this->queueService->setStatusByField('channel', $datum[0]['channel'], QueueStatus::CANCEL());
                logger_info("CDR UPLOAD implement case_13 [{$datum[0]['uniqueid']}]");
            } elseif (
                // case 17
                count($datum) == 1
                && array_key_exists(CdrEntity::STATUS_CANCEL, $valuesReasonHangup)
            ) {
                $recs[] = array_merge($datum[0], [
                    'dst' => is_numeric($datum[0]['dst']) ? $datum[0]['dst'] : '',
                    'disposition' => CdrEntity::STATUS_CANCEL,
                    'employee_id' => $employeeIDs[$datum[0]['employee_uuid']] ?? null,
                    'from_employee_id' => $employeeIDs[$datum[0]['from_employee_uuid']] ?? null,
                    'department_id' => $departmentsIDs[$datum[0]['department_uuid']] ?? null
                ]);

                logger_info("CDR UPLOAD implement case_17 [{$datum[0]['uniqueid']}]");
            } else {
                $recs[0] = array_merge($datum[0], [
                    'employee_id' => $employeeIDs[$datum[0]['employee_uuid']] ?? null,
                    'from_employee_id' => $employeeIDs[$datum[0]['from_employee_uuid']] ?? null,
                    'department_id' => $departmentsIDs[$datum[0]['department_uuid']] ?? null
                ]);

                logger_info("CDR UPLOAD implement case_5 or have one record [{$datum[0]['uniqueid']}]");
            }
        }

        // записываем данные в историю
        foreach ($recs as $rec){
            $this->historyService->create(HistoryDto::byArgs($rec));
        }
        // вытянутым данным из cdr, проставляем что они уже обработаны
        foreach ($datum as $item){
            $this->updateIsFetch(
                $item['uniqueid'],
                $item['calldate'],
            );
        }
    }

    public function uploadBackgroundData()
    {
        $data = [];
        $this->getConectionDb()
            ->whereIn('lastapp', [
                CdrEntity::TYPE_BACKGROUND,
                CdrEntity::TYPE_PLAYBACK,
                CdrEntity::TYPE_HANGUP,
            ])
            ->where(function (Builder $q){
                return $q->where('is_fetch', '!=', 1)
                    ->orWhereNull('is_fetch');
            })
            ->orderBy('calldate')
            ->get()
            ->each(function ($item) use (&$data) {
                $item = stdToArray($item);
                $data[$item['uniqueid']][] = $item;
            });

        foreach ($data as $datum){
            $recs = [];
            // получаем какие значения есть в полях - true_reason_hangup, для конкретного - uniqueid, чтоб узнать есть ли трансфер звонка
            $valuesReasonHangup = array_flip(array_diff(array_column($datum, 'true_reason_hangup'), ['', NULL, false]));
            $valuesLastApp = array_flip(array_diff(array_column($datum, 'lastapp'), ['', NULL, false]));

            if(
                // case 17
                count($datum) == 1
                && array_key_exists(CdrEntity::STATUS_CANCEL, $valuesReasonHangup)
                && array_key_exists(CdrEntity::TYPE_BACKGROUND, $valuesLastApp)
            ){
                $recs[] = array_merge($datum[0], [
                    'dst' => is_numeric($datum[0]['dst']) ? $datum[0]['dst'] : '',
                    'disposition' => CdrEntity::STATUS_CANCEL,
                    'employee_id' => null,
                    'department_id' => null
                ]);
                logger_info("CDR UPLOAD implement case_17 for background [{$datum[0]['uniqueid']}]");
            } elseif (
                // case 20
                count($datum) == 1
                && array_key_exists(CdrEntity::STATUS_NO_ANSWER, $valuesReasonHangup)
                && (
                    array_key_exists(CdrEntity::TYPE_PLAYBACK, $valuesLastApp)
                    || array_key_exists(CdrEntity::TYPE_HANGUP, $valuesLastApp)
                )
            ){
                $recs[] = array_merge($datum[0], [
                    'disposition' => CdrEntity::STATUS_NO_ANSWER,
                    'employee_id' => null,
                    'department_id' => null
                ]);
                logger_info("CDR UPLOAD implement case_20 for background [{$datum[0]['uniqueid']}]");
            }

            // записываем данные в историю
            foreach ($recs as $rec){
                $this->historyService->create(HistoryDto::byArgs($rec));
            }
            // вытянутым данным из cdr, проставляем что они уже обработаны
            foreach ($datum as $item){
                $this->updateIsFetch(
                    $item['uniqueid'],
                    $item['calldate'],
                );
            }
        }
    }

    public function create(array $data)
    {
        $this->insert($data);
    }

    public function updateIsFetch(string $uniqueid, string $calldate)
    {
        return $this->getConectionDb()
            ->where('uniqueid', $uniqueid)
            ->where('calldate', $calldate)
            ->update(['is_fetch' => 1]);
    }

    public function updateIsFail(string $uniqueid)
    {
        return $this->getConectionDb()
            ->where('uniqueid', $uniqueid)
            ->update(['is_fetch' => 2]);
    }

    public static function parseNumFromChanel(string $value): ?string
    {
        $parse = preg_replace("/.*\/(.*?)@.*/", '$1', $value);
        if($parse == $value){
            return null;
        }

        return $parse;
    }

    public static function parseNameFromClid(?string $value): ?string
    {
        if(!$value){
            return null;
        }

        $name = preg_replace("/.*\"(.*?)\".*/", '$1', $value);

        if($name == ""){
            $name = null;
        }

        return $name;
    }
}
