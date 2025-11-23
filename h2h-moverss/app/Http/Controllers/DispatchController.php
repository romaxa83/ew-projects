<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dispatch\Ajax\LogRequest;
use App\Models\{Audit, Client\Phone, DispatchEmployer, DispatchTruck, Employee, Order, Truck, WorkTypes};
use App\Services\Audit\AuditFetchService;
use Carbon\{Carbon, CarbonImmutable, CarbonPeriod};
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Mailjet\{Client, Resources};
use phpDocumentor\Reflection\Types\Collection;

/**
 * Managing the distribution of orders for employees and trucks
 */
class DispatchController extends Controller
{
    public const DISPATCH_DAY_START_AT = '07:00:00';
    public const DISPATCH_DAY_END_AT = '21:59:59';
    public const DISPATCH_COL_MINUTES = 30;

    /**
     * Calendar for day with trucks and crews.
     * @param  Request  $request
     * @return Renderable
     */
    public function schedule(Request $request): Renderable
    {
        $date = $request->filled('start_date') ? CarbonImmutable::parse($request->start_date) : CarbonImmutable::now();

        $dispatchStart = $date->setTimeFromTimeString(self::DISPATCH_DAY_START_AT);
        $dispatchEnd = $dispatchStart->copy()->setTimeFromTimeString(self::DISPATCH_DAY_END_AT);

        $dispatchPeriod = CarbonPeriod::since($dispatchStart)->minutes(30)->until($dispatchEnd);

        $assignedWorks = Order\Work::where('start_date', '=', $dispatchStart->toDateString())
            ->with([
                'dispatchTrucks:work_id,truck_id',
                'dispatchEmployees:work_id,employer_id',
            ])
            ->whereHas('order', function ($q) {
                $q->whereJsonContains('division_id', request()->session()->get('division.id'));
            })
            ->get(['id']);

        $trucks = Truck\Truck::query()
            ->partnerTrucks()
            ->activeDispatch($assignedWorks)
            ->get()
            ->keyBy('id')
        ;
        $employees = Employee::ActiveDispatch($assignedWorks)->get();

        return view('layouts.dispatch.body', [
            'date' => $date,
            'dispatchPeriod' => $dispatchPeriod,
            'trucks' => $trucks,
            'workers' => $employees->keyBy('id'),
            'workersBusy' => $this->busyDates('employee', $dispatchStart),
            'trucksBusy' => $this->busyDates('truck', $dispatchStart),
            'user' => \Auth::user(),
        ]);
    }

    /**
     * Busyness of trucks / employees.
     * @param  string  $selection  truck or employee
     * @param  Carbon|CarbonImmutable  $date
     * @return array
     */
    private function busyDates(string $selection, Carbon|CarbonImmutable $date): array
    {
        // Get max visible cells
        $diff_in_seconds = abs(strtotime(self::DISPATCH_DAY_END_AT) - strtotime(self::DISPATCH_DAY_START_AT));
        $day_hours_max = ceil($diff_in_seconds / 3600) * 2;

        $dt = $date->copy()->modify('00:00:00');

        if ($selection === 'truck') {
            $key = 'truck_id';

            $records = Truck\Busy::query();
            $records_w_day = Truck\BusyWeekDay::query();
        } else {
            $key = 'employee_id';

            $records = Employee\Busy::query();
            $records_w_day = Employee\BusyWeekDay::query();
        }


        // Busyness by dates
        $records = $records->where([
            ['start_date', '<=', $dt],
            ['end_date', '>=', $dt->copy()->modify('23:59:59')],
        ])
            ->orWhereBetween('start_date', [$dt, $date->modify('23:59:59')->format('Y-m-d H:i:s')])
            ->orWhereBetween('end_date', [$dt, $date->modify('23:59:59')->format('Y-m-d H:i:s')])
            ->get();

        $busy = [];
        foreach ($records as $v) {
            if (!$v->start_date->isSameDay($date) && !$v->end_date->isSameDay($date)) {
                // All day is busy
                $busy[$v->{$key}][] = [
                    'from' => 1,
                    'duration' => $day_hours_max,
                    'title' => $v->reason,
                ];
            } elseif ($v->start_date->isSameDay($date)) {
                // Start + End in this day
                $from = ($date->copy()->modify(self::DISPATCH_DAY_START_AT))->diffInMinutes($v->start_date) / self::DISPATCH_COL_MINUTES;

                if ((int) $v->start_date->diff($v->end_date)->format('%d')) {
                    // More than one day busy
                    $duration = $day_hours_max;
                } else {
                    $duration = $v->start_date->diffInMinutes($v->end_date) / self::DISPATCH_COL_MINUTES;
                }

                $busy[$v->{$key}][] = [
                    'from' => $from < 1 ? 1 : 1 + $from,
                    'duration' => $duration,
                    'title' => $v->reason,
                ];
            } elseif ($v->end_date->isSameDay($date)) {
                // End on this day
                $duration = ($date->copy()->modify(self::DISPATCH_DAY_START_AT))->diffInMinutes($v->end_date) / self::DISPATCH_COL_MINUTES;

                $busy[$v->{$key}][] = [
                    'from' => 1,
                    'duration' => min($duration, $day_hours_max),
                    'title' => $v->reason,
                ];
            }
        }

        // Busyness by days of the week
        $records = $records_w_day->whereNotNull('miscs')->get([$key, 'miscs']);
        if ($records) {
            foreach ($records as $v) {
                if (in_array(now()->dayOfWeek, $v->miscs, true)) {
                    $busy[$v->{$key}][] = [
                        'from' => 1,
                        'duration' => $day_hours_max,
                        'title' => 'Busy Week Day',
                    ];
                }
            }
        }

        return $busy;
    }

    /**
     *
     * @param $EmployeeID
     * @param  CarbonImmutable|Carbon  $workStart
     * @param  CarbonImmutable|Carbon  $workEnd
     * @param  null|array|Collection  $ignoredWorks
     * @return bool
     */
    private function isEmployeeAvailableBetweenDates(
        $EmployeeID,
        CarbonImmutable|Carbon $workStart,
        CarbonImmutable|Carbon $workEnd,
        $ignoredWorks = null
    ) {
//        $worksCount = DispatchEmployer::where('employer_id', $EmployeeID)
//            ->whereHas('work', function (Builder $query) use ($workStart, $workEnd) {
//                $query->whereBetween(DB::raw("CAST(CONCAT(`start_date`, ' ', `start_time`) as DATETIME)"), [$workStart, $workEnd])
//                    ->orWhereBetween(DB::raw("DATE_ADD(CAST(CONCAT(`start_date`, ' ', `start_time`) as DATETIME), interval `duration` hour)"),
//                        [$workStart, $workEnd])
//                    ->orWhereRaw("CAST('" . $workStart->toDateTimeString() . "' as DATETIME) BETWEEN " .
//                        "CAST(CONCAT(`start_date`, ' ', `start_time`) as DATETIME) AND " .
//                        "DATE_ADD(CAST(CONCAT(`start_date`, ' ', `start_time`) as DATETIME), interval `duration` hour)")
//                    ->orWhereRaw("CAST('" . $workEnd->toDateTimeString() . "' as DATETIME) BETWEEN " .
//                        "CAST(CONCAT(`start_date`, ' ', `start_time`) as DATETIME) AND " .
//                        "DATE_ADD(CAST(CONCAT(`start_date`, ' ', `start_time`) as DATETIME), interval `duration` hour)");
//            });
//        if ($ignoredWorks)
//            foreach ($ignoredWorks as $Work) {
//                $worksCount->where('work_id', '<>', $Work->id);
//            }

        // holidays or busy times
        $busyCount = Employee\Busy::where('employee_id', $EmployeeID)->where(function (Builder $query) use (
            $workStart,
            $workEnd
        ) {
            $query->whereBetween('start_date', [$workStart, $workEnd])->orWhereBetween('end_date',
                [$workStart, $workEnd]);
        });

//        return !$worksCount->count() && !$busyCount->count();
        return !$busyCount->count();
    }


    /**
     * Check truck availability
     * @param $truckID
     * @param  CarbonImmutable|Carbon  $workStart
     * @param  CarbonImmutable|Carbon  $workEnd
     * @param  null|array|Collection  $ignoredWorks
     * @return bool
     */
    private function isTrackAvailableBetweenDates(
        $truckID,
        CarbonImmutable|Carbon $workStart,
        CarbonImmutable|Carbon $workEnd,
        $ignoredWorks = null
    ) {
//        $worksCount = DispatchTruck::whereTruckId($truckID)
//            ->whereHas('work', function (Builder $query) use ($workStart, $workEnd) {
//                $query->whereBetween(DB::raw("CAST(CONCAT(`start_date`, ' ', `start_time`) as DATETIME)"), [$workStart, $workEnd])
//                    ->orWhereBetween(DB::raw("DATE_ADD(CAST(CONCAT(`start_date`, ' ', `start_time`) as DATETIME), interval `duration` hour)"),
//                        [$workStart, $workEnd])
//                    ->orwhereRaw("CAST('" . $workStart->toDateTimeString() . "' as DATETIME) BETWEEN " .
//                        "CAST(CONCAT(`start_date`, ' ', `start_time`) as DATETIME) AND " .
//                        "DATE_ADD(CAST(CONCAT(`start_date`, ' ', `start_time`) as DATETIME), interval `duration` hour)")
//                    ->orWhereRaw("CAST('" . $workEnd->toDateTimeString() . "' as DATETIME) BETWEEN " .
//                        "CAST(CONCAT(`start_date`, ' ', `start_time`) as DATETIME) AND " .
//                        "DATE_ADD(CAST(CONCAT(`start_date`, ' ', `start_time`) as DATETIME), interval `duration` hour)");
//
//            });
//        if ($ignoredWorks)
//            foreach ($ignoredWorks as $Work) {
//                $worksCount->where('work_id', '<>', $Work->id);
//            }
        // holidays or busy times
        $busyCount = Truck\Busy::whereTruckId($truckID)->where(function (Builder $query) use ($workStart, $workEnd) {
            $query->whereBetween('start_date', [$workStart, $workEnd])->orWhereBetween('end_date',
                [$workStart, $workEnd]);
        });

//        return !$worksCount->count() && !$busyCount->count();
        return !$busyCount->count();
    }


    /**
     * Save the distribution of trucks and employees
     * @param  Request  $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function save(Request $request): JsonResponse
    {
        $data = $request->all();

        $works = $data['works'];
        $date = Carbon::parse($data['start_date'])->toDateString();

        $changed = 0;

        try {
            if ($data['updated_at'] !== $this->getLatestUpdatedWork($date)) {
                throw new Exception('Someone has saved the data before you');
            }

            DB::transaction(function () use ($works, &$changed) {
                foreach ($works as $v) {
                    $work = Order\Work::with([
                        'dispatchTrucks',
                        'dispatchEmployees',
                    ])->findOrFail($v['id']);
                    $workStart = new CarbonImmutable($work->start_date.' '.$work->start_time);
                    $workEnd = $workStart->addHours(+$work->duration);
                    if (!empty($v['dispatch_trucks'])) {
                        foreach ($v['dispatch_trucks'] as $dispatch_record) {
                            if (
                                $dispatch_record['truck_id']
                                && !$this->isTrackAvailableBetweenDates($dispatch_record['truck_id'], $workStart, $workEnd, [$work]))
                            {
                                $Truck = Truck\Truck::findOrFail($dispatch_record['truck_id']);
                                // проверить, нет ли у работы назначения УЖЕ на другой трак

                                $msg = "Truck {$Truck->title} [$Truck->nickname] not available between ".
                                    $workStart->format('M d, g:i a')." and ".$workEnd->format('M d, g:i a');
                                throw new Exception($msg);
                            }
//                            throw new Exception('test');
                        }
                    }
                    if (!empty($v['dispatch_employees'])) {
                        foreach ($v['dispatch_employees'] as $dispatch_record) {
                            if (
                                $dispatch_record['employer_id']
                                && !$this->isEmployeeAvailableBetweenDates($dispatch_record['employer_id'], $workStart, $workEnd, [$work])
                            ) {
                                $Employee = Employee::findOrFail($dispatch_record['employer_id']);
                                $msg = "Employee \"{$Employee->name} {$Employee->l_name}\" not available between ".
                                    $workStart->format('M d, g:i a')." and ".$workEnd->format('M d, g:i a');
                                throw new Exception($msg);
                            }

                        }
                    }

//                    if($v['order_id'] == '142249'){
                        $changed += $work->updateDispatchRelations('dispatchEmployees', 'employer_id', $v['dispatch_employees']);

                        $this->normalizeAuditForEmployee($v, $work->order_id);

                        $changed += $work->updateDispatchRelations('dispatchTrucks', 'truck_id', $v['dispatch_trucks']);

                        $this->normalizeAuditForTruck($v, $work->order_id);
//                    }
                }
            });
        } catch (Exception $e) {
            return response()
                ->json([
                    'success' => false,
                    'msg' => $e->getMessage(),
                ]);
        }

        return response()
            ->json([
                'success' => true,
                'msg' => $changed ? 'Changed' : 'Changed nothing',
                'updated_at' => $this->getLatestUpdatedWork($date),
            ]);
    }

    private function normalizeAuditForEmployee(
        array $data,
        int $orderId
    ): void
    {
        $oldDispatchEmployeesIds = $data['dispatch_employees_ids'];
        $newDispatchEmployeesIds = array_map(function($item) {
            return $item['employer_id'];
        }, $data['dispatch_employees']);

        asort($oldDispatchEmployeesIds);
        asort($newDispatchEmployeesIds);

        if(hash_data($oldDispatchEmployeesIds) != hash_data($newDispatchEmployeesIds)){
            $audit = Audit::query()
                ->where('order_id', $orderId)
                ->where('auditable_type', DispatchEmployer::MORPH_NAME)
                ->latest('created_at')
                ->first();

            if($audit){
                if(
                    empty($audit->old_values)
                    && !empty($newDispatchEmployeesIds)
                ){
                    // если нет old_values, добавляем значения в него
                    $values = $audit->new_values;
                    unset($values['employer_id']);
                    $newValues = $values + ['employee_ids' => implode(',', $newDispatchEmployeesIds)];
                    $oldValues = $values + ['employee_ids' => implode(',', $oldDispatchEmployeesIds)];

                    $tmp = [
                        'new_values' => $newValues,
                        'event' => 'created'
                    ];
                    if(!empty($oldDispatchEmployeesIds)){
                        $tmp['event'] = 'updated';
                        $tmp['old_values'] = $oldValues;
                    }

                    $audit->update($tmp);
                } elseif (
                    !empty($oldDispatchEmployeesIds)
                    && empty($newDispatchEmployeesIds)
                ){
                    // это кейс когда удалили всех сотрудников из заказа
                    $newAudit = new Audit();
                    $newAudit->user_type = $audit->user_type;
                    $newAudit->user_id = auth_user()->id;
                    $newAudit->order_id = $audit->order_id;
                    $newAudit->client_id = $audit->client_id;
                    $newAudit->event = 'deleted';
                    $newAudit->auditable_type = $audit->auditable_type;
                    $newAudit->auditable_id = $audit->auditable_id;
                    $newAudit->old_values = [
                        'work_id' => $audit->new_values['work_id'] ?? null,
                        'id' => $audit->new_values['id'] ?? null,
                        'employee_ids' => $oldDispatchEmployeesIds[0],
                    ];
                    $newAudit->new_values = [];
                    $newAudit->url = $audit->url;
                    $newAudit->ip_address = $audit->ip_address;
                    $newAudit->user_agent = $audit->user_agent;
                    $newAudit->tags = $audit->tags;
                    $newAudit->dispatch_truck_at = $audit->dispatch_truck_at;
                    $newAudit->save();
                } else {
                    // если есть old_values, значит это старая запись и новая не создалась,
                    // такое поведение наблюдается при удалении мувера,
                    // создаем новую запись в ручную на основе старой

                    $values = $audit->new_values;

                    $newValues = $values;
                    $newValues['employee_ids'] = implode(',', $newDispatchEmployeesIds);

                    $oldValues = $values;
                    $oldValues['employee_ids'] = implode(',', $oldDispatchEmployeesIds);

                    $newAudit = new Audit();
                    $newAudit->user_type = $audit->user_type;
                    $newAudit->user_id = auth_user()->id;
                    $newAudit->order_id = $audit->order_id;
                    $newAudit->client_id = $audit->client_id;
                    $newAudit->event = $audit->event;
                    $newAudit->auditable_type = $audit->auditable_type;
                    $newAudit->auditable_id = $audit->auditable_id;
                    $newAudit->old_values = $oldValues;
                    $newAudit->new_values = $newValues;
                    $newAudit->url = $audit->url;
                    $newAudit->ip_address = $audit->ip_address;
                    $newAudit->user_agent = $audit->user_agent;
                    $newAudit->tags = $audit->tags;
                    $newAudit->dispatch_truck_at = $audit->dispatch_truck_at;
                    $newAudit->save();
                }
            }
        }
    }

    private function normalizeAuditForTruck(
        array $data,
        int $orderId
    ): void
    {
        // когда на панели диспетчеров переключаются траки, он создает новую запись,
        // и не фиксирует старые данные (с какого трака переключили)
        $oldDispatchTrucksIds = $data['dispatch_trucks_ids'];
        $newDispatchTrucksIds = array_map(function($item) {
            return $item['truck_id'];
        }, $data['dispatch_trucks']);

        asort($oldDispatchTrucksIds);
        asort($newDispatchTrucksIds);

        if(hash_data($oldDispatchTrucksIds) != hash_data($newDispatchTrucksIds)){
            $audit = Audit::query()
                ->where('order_id', $orderId)
                ->where('auditable_type', DispatchTruck::MORPH_NAME)
                ->latest('created_at')
                ->first();

            if($audit){
                if(empty($newDispatchTrucksIds) && !empty($oldDispatchTrucksIds)){
                    // последний трак был удален из заказа, вручную создаем запись для аудита
                    $newAudit = new Audit();
                    $newAudit->user_type = $audit->user_type;
                    $newAudit->user_id = auth_user()->id;
                    $newAudit->order_id = $audit->order_id;
                    $newAudit->client_id = $audit->client_id;
                    $newAudit->event = 'deleted';
                    $newAudit->auditable_type = $audit->auditable_type;
                    $newAudit->auditable_id = $audit->auditable_id;
                    $newAudit->old_values = [
                        'work_id' => $audit->new_values['work_id'] ?? null,
                        'id' => $audit->new_values['id'] ?? null,
                        'truck_ids' => $oldDispatchTrucksIds[0],
                    ];
                    $newAudit->new_values = [];
                    $newAudit->url = $audit->url;
                    $newAudit->ip_address = $audit->ip_address;
                    $newAudit->user_agent = $audit->user_agent;
                    $newAudit->tags = $audit->tags;
                    $newAudit->dispatch_truck_at = $audit->dispatch_truck_at;
                    $newAudit->save();

                } elseif (
                    count($oldDispatchTrucksIds) > count($newDispatchTrucksIds)
                ) {
                    // один из траков был удален, вручную создаем запись
                    $newAudit = new Audit();
                    $newAudit->user_type = $audit->user_type;
                    $newAudit->user_id = auth_user()->id;
                    $newAudit->order_id = $audit->order_id;
                    $newAudit->client_id = $audit->client_id;
                    $newAudit->event = 'updated';
                    $newAudit->auditable_type = $audit->auditable_type;
                    $newAudit->auditable_id = $audit->auditable_id;
                    $newAudit->old_values = [
                        'work_id' => $audit->new_values['work_id'] ?? null,
                        'id' => $audit->new_values['id'] ?? null,
                        'truck_ids' => implode(',', $oldDispatchTrucksIds),
                    ];
                    $newAudit->new_values = [
                        'work_id' => $audit->new_values['work_id'] ?? null,
                        'id' => $audit->new_values['id'] ?? null,
                        'truck_ids' => implode(',', $newDispatchTrucksIds),
                    ];
                    $newAudit->url = $audit->url;
                    $newAudit->ip_address = $audit->ip_address;
                    $newAudit->user_agent = $audit->user_agent;
                    $newAudit->tags = $audit->tags;
                    $newAudit->dispatch_truck_at = $audit->dispatch_truck_at;

                    $newAudit->save();
                } else {
                    $values = $audit->new_values;
                    unset($values['truck_id']);
                    $newValues = $values + ['truck_ids' => implode(',', $newDispatchTrucksIds)];
                    $oldValues = $values + ['truck_ids' => implode(',', $oldDispatchTrucksIds)];

                    $tmp = [
                        'new_values' => $newValues,
                        'event' => 'created'
                    ];
                    if(!empty($oldDispatchTrucksIds)){
                        $tmp['event'] = 'updated';
                        $tmp['old_values'] = $oldValues;
                    }
                    $audit->update($tmp);
                }
            }
        }
    }


    // номер колонки на диспатче. Начинается с 1
    private function calcDispatchStart(Order\Work $item, Carbon|CarbonImmutable $DispatchDay)
    {
//        $startCol = 1;
        $DispatchDayStart = $DispatchDay->startOfDay()->setTimeFromTimeString(self::DISPATCH_DAY_START_AT);
//        $startColHourDiff = $DispatchDayStart->format('H') - 1;
        $startWork = (new CarbonImmutable($item->start_date.' '.$item->start_time))->startOfMinute();
        // starts earlier than dispatchday
        if ($startWork < $DispatchDayStart) {
            $startCol = 1;
        } else {
            $startCol = ($startWork->diffInMinutes($DispatchDayStart)) / self::DISPATCH_COL_MINUTES + 1;
        }
        return round($startCol);
    }

    // кол-во колонок, на которое тянется работа
    private function calcDispatchDuration(Order\Work $item, Carbon|CarbonImmutable $DispatchDay)
    {
        $maxDayDuration = ($DispatchDay->setTimeFromTimeString(self::DISPATCH_DAY_END_AT)
                ->diffInMinutes($DispatchDay->setTimeFromTimeString(self::DISPATCH_DAY_START_AT))) / self::DISPATCH_COL_MINUTES;
        $maxDayDuration = (int) ceil($maxDayDuration);
        $DispatchDayStart = $DispatchDay->startOfDay()->setTimeFromTimeString(self::DISPATCH_DAY_START_AT);
        $DispatchDayEnd = $DispatchDay->endOfDay()->setTimeFromTimeString(self::DISPATCH_DAY_END_AT);
        $duration = ceil(+60 * $item->duration); // to minutes
        if ($item->start_time_to) {
            $duration += abs((new Carbon($item->start_time))->startOfMinute()->diffInMinutes(new Carbon($item->start_time_to)));
        }
        // if overlength - strip to the day end
        $startWork = (new CarbonImmutable($item->start_date.' '.$item->start_time))->startOfMinute();
        $endWork = $startWork->addMinutes($duration);
        // same day at dispatch day
        if ($startWork >= $DispatchDayStart && $endWork <= $DispatchDayEnd) {
            //return $duration;
            // starts earlier than dispatchday
        } elseif ($startWork < $DispatchDayStart && $endWork <= $DispatchDayEnd) {
            $duration = min($endWork, $DispatchDay->setTimeFromTimeString(self::DISPATCH_DAY_END_AT))
                ->diffInMinutes($DispatchDay->setTimeFromTimeString(self::DISPATCH_DAY_START_AT));
            // ends later than dispatchday
        } elseif ($startWork >= $DispatchDayStart && $endWork >= $DispatchDayEnd) {
//            dd($DispatchDay->setTimeFromTimeString(self::DISPATCH_DAY_END_AT)->diffInHours($startWork));
            $duration = $DispatchDay->setTimeFromTimeString(self::DISPATCH_DAY_END_AT)->diffInMinutes($startWork);
        } elseif ($startWork <= $DispatchDayStart && $endWork >= $DispatchDayEnd) {
            return $maxDayDuration;
        }

        return round($duration / self::DISPATCH_COL_MINUTES);
    }


    /**
     * Data to build the AJAX output.
     * @param  Request  $request
     * @return JsonResponse
     */
    public function ajaxInfo(Request $request): JsonResponse
    {
        $currentDivision = session('division');

        $date = CarbonImmutable::parse($request->start_date)->setTimeFromTimeString(self::DISPATCH_DAY_START_AT);
        $dispatchStart = $date->setTimeFromTimeString(self::DISPATCH_DAY_START_AT);
//        $dispatchEnd = $date->setTimeFromTimeString(self::DISPATCH_DAY_END_AT);
//        $dispatchPeriod = [];
//        foreach (CarbonPeriod::since($dispatchStart)->minutes(60)->until($dispatchEnd) as $item) {
//            $dispatchPeriod[] = $item->format('g a');
//        }

        $assignedWorks = Order\Work::where('start_date', '=', $dispatchStart->toDateString())
            ->with([
                'dispatchTrucks:work_id,truck_id',
                'dispatchEmployees:work_id,employer_id',
            ])
            ->get(['id']);
        $employees = Employee::activeDispatch($assignedWorks)->get();
//dd($assignedWorks);

        $works = Order\Work::where(function (Builder $query) use ($date) {
            $query->where('start_date', $date->toDateString())
                ->orWhereRaw("'".$date->toDateTimeString()."' BETWEEN CAST(CONCAT(`start_date`, ' ', `start_time`) as DATETIME) AND ".
                    "DATE_ADD(CAST(CONCAT(`start_date`, ' ', `start_time`) as DATETIME), interval `duration` hour)");
            // or work e
        })
            ->withTotalPayments()
            ->with([
                'order.estimate.local:order_id,rate',
                'order.estimate.intrastate:order_id,rate',
                'order.estimate.interstate:order_id,rate,estimate_rate',
                'order.status:id,color,title',
            ])
            ->dispatch()
            ->get()
            ->keyBy('id')
            ->transform(function ($item) use ($date) {
                $item->order->sizing_volume *= 1;
                $item->order->sizing_weight *= 1;
                $item->dispatch_duration = $this->calcDispatchDuration($item, $date);
                $item->dispatch_col_start = $this->calcDispatchStart($item, $date);
                return $item;
            });
//            ->filter(function ($item) use ($date) {
//                // Если это продление дичпатча с предыдущего дня
//                return !(!Carbon::parse($item->start_date)->isSameDay($date) && ($item->dispatch_employees || $item->dispatch_trucks));
//            });
//        dd($works->toArray());
        // works transform

        $service = resolve(AuditFetchService::class);
        if($request->logs_all){
            $logs = $service->byDispatchList([
                'start_date' => $request->start_date,
                'division_id' => $currentDivision['id'] ?? null
            ]);
        } else {
            $logs = $service->byDispatchPagination([
                'start_date' => $request->start_date,
                'division_id' => $currentDivision['id'] ?? null
            ]);
        }

        return response()
            ->json([
                'success' => true,
                'types' => [
                    'works' => WorkTypes::get(['id', 'title'])->keyBy('id'),
                ],
                'works' => $works,
                'trucks' => Truck\Truck::query()
                    ->whereJsonContains(
                        'division_ids',
                        request()->session()->get('division.id'))->orderBy('title'
                    )
                    ->partnerTrucks()
                    ->get([
                        'id',
                        'title',
                        'p_color',
                        'active',
                        'partner_id',
                    ])
                    ->keyBy('id'),
//                'dispatchPeriod' => $dispatchPeriod,
                'workers' => $employees->keyBy('id'),
                'workersBusy' => $this->busyDates('employee', $dispatchStart),
                'updated_at' => $this->getLatestUpdatedWork($date->toDateString()),
                'logs' => $logs,
            ]);
    }

    public function ajaxLogs(LogRequest $request): JsonResponse
    {
        $service = resolve(AuditFetchService::class);

        if($request->logs_all){
            $logs = $service->byDispatchList($request->validated());
        } else {
            $logs = $service->byDispatchPagination($request->validated());
        }

        return response()
            ->json([
                'success' => true,
                'logs' => $logs,
            ]);
    }

    /**
     * Send mass notify about joining to dispatch.
     * @param  Request  $request
     * @return JsonResponse
     */
    public function sendNotifyToAll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'type' => 'required|in:all,unnotofied',
        ]);

        // getting data
        $dispatched = DispatchEmployer::query()
            ->whereHas('work', function (Builder $q) use ($validated) {
                return $q->where('start_date', $validated['date']);
            })
            ->with(['employee', 'work', 'work.order', 'work.order.manager', 'employee.emails'])
            ->get();

        $MessageBag = new MessageBag();
        if (!$dispatched) {
            return response()
                ->json([
                    'success' => false,
                    'message' => Carbon::parse($validated['date'])->format('g:i A').' has no dispatched staff!',
                ]);
        }

        $cnt = 0;
        $dispatched->each(function (DispatchEmployer $DispatchedEmployer) use ($validated, $MessageBag, &$cnt) {
            try {
                $miscs = (array) $DispatchedEmployer->miscs;
                if (!empty($miscs['notify']) && $validated['type'] === 'unnotofied') {
                    return true;
                }
                $work = Order\Work::find($DispatchedEmployer->work->id);
                if ($work) {
                    $this->sendOrderAssignedNotify($DispatchedEmployer->work->order, $work, $DispatchedEmployer);
                    $cnt++;
                } else {
                    $MessageBag->add($DispatchedEmployer->work->id.'_'.$DispatchedEmployer->employee->id,
                        'Work #'.$DispatchedEmployer->work->id.' no longer exists');
                }
            } catch (Exception $e) {
                $MessageBag->add($DispatchedEmployer->work->id.'_'.$DispatchedEmployer->employee->id, $e->getMessage());
            }
        });

        return response()
            ->json([
                'success' => true,
                'count' => $cnt,
                'msgs' => $MessageBag->all(),
            ]);
    }

    /**
     * Send assigned notify.
     * @param  Order  $Order
     * @param  Order\Work  $Work
     * @param  DispatchEmployer  $DispatchedEmployer
     * @return array
     * @throws Exception
     */
    private function sendOrderAssignedNotify(Order $Order, Order\Work $Work, DispatchEmployer $DispatchedEmployer)
    {
        /**
         * @var $Employee Employee
         */
        $Employee = $DispatchedEmployer->employee;

        if (!($email = $Employee->emails->first())) {
            throw new \Exception("Employee [{$Employee->id}] ".$Employee->name.' '.$Employee->l_name.' has no email!');
        }
        $work_notes = [];
        $waypoints_notes = [];
        $Order->load(['waypoints.notes', 'client.phones', 'payments']);

        if (!empty($Work->notes)) {
            $work_notes[] = [
                'title' => 'Note for Service',
                'text' => $Work->notes,
            ];
        }
        $Work->load([
            'dispatchTrucks:truck_id,work_id',
            'dispatchTrucks.truck:id,title',
            'dispatchEmployees:employer_id,work_id',
            'dispatchEmployees.employee:id,name,l_name',
            'dispatchEmployees.employee.phones' => function ($query) {
                return $query->orderBy('is_primary', 'DESC')->orderBy('sort', 'ASC');
            },
        ]);
        $orderTrucks = '';
        $orderDeposit = 0;
        if ($Order->payments && $Order->payments->isNotEmpty()) {
            $orderDeposit = $Order->payments->sum(function ($payment) {
                return $payment->in_total_sum ? +$payment->amount : 0;
            });
        }

        if ($Work->dispatchTrucks && $Work->dispatchTrucks->isNotEmpty()) {
            $trucks = [];
            foreach ($Work->dispatchTrucks as $Truck) {
                $trucks[] = $Truck->truck->title;
            }
            $orderTrucks = implode(', ', $trucks);
        }
        if ($Work->dispatchEmployees && $Work->dispatchEmployees->isNotEmpty()) {
            $crew = [];
            foreach ($Work->dispatchEmployees as $Employee) {
                $member = ['name' => $Employee->employee->name.' '.$Employee->employee->l_name, 'phones' => []];
                if ($Employee->employee->phones && $Employee->employee->phones->isNotEmpty()) {
                    foreach ($Employee->employee->phones as $Phone) {
                        $phone = Phone::getInternationalPhoneNumber($Phone->value, 'US');
                        $member['phones'][] = [
                            'value' => preg_replace('/[^0-9\+]/', '', $phone),
                            'formatted' => $phone,
                        ];
                    }
                }
                $crew[] = $member;
            }
        }

        if ($Order->waypoints->isNotEmpty()) {
            foreach ($Order->waypoints as $waypoint) {
                if ($waypoint->notes && $waypoint->notes->isNotEmpty()) {
                    foreach ($waypoint->notes as $note) {
                        $waypoints_notes[] = [
                            'title' => 'Note for address. '.$waypoint->address,
                            'text' => $note->value,
                        ];
                    }
                }
            }
        }


        $Order->work = [
            'start_date' => Carbon::parse($Work->start_date)->format('m/d/Y'),
            'start_time' => $Work->start_time_to ?
                Carbon::parse($Work->start_time)->format('g:i A').' - '.Carbon::parse($Work->start_time_to)->format('g:i A') :
                Carbon::parse($Work->start_time)->format('g:i A'),
            'work_types' => $Work->workTypes->implode('title', ', '),
        ];
        $Order->href = route('customer.orderPublicView', ['hash' => $Order->hash]);

        $mj = new Client(config('app.mail_jet.public'), config('app.mail_jet.private'), true, ['version' => 'v3.1']);

        $phones = [];
        if ($Order->client) {
            if ($Order->client->phones && $Order->client->phones->isNotEmpty()) {
                foreach ($Order->client->phones as $Phone) {
                    $phone = Phone::getInternationalPhoneNumber($Phone->value, 'US');
                    $phones[] = [
                        'formatted' => $phone,
                        'value' => preg_replace('/[^0-9\+]/', '', $phone),
                    ];
                }
            }
        }

        $body = [
            'Messages' => [
                [
                    'To' => [
                        [
                            'Email' => $email->value,
                            'Name' => $Employee->name,
                        ],
                    ],
                    'TemplateID' => 4950660,
                    'TemplateLanguage' => true,
                    'TemplateErrorReporting' => [
                        'Email' => 'vladimir.goncharuk@gmail.com',
                        'Name' => 'dobs',
                    ],
                    'Variables' => [
//                        'ORDER' => [ // Нужен этот костыль верхним регистром т.к. без него не цепляло переменных
//                            'ID' => 'TEST',
//                        ],
//                        'COMMENTS' => [ // Нужен этот костыль верхним регистром т.к. без него не цепляло переменных
//                            'has_waypoints_comments' => 'TEST',
//                        ],
                        'trucks' => $orderTrucks,
                        'crew' => $crew,
                        'deposit' => $orderDeposit,
                        'client' => [
                            'name' => $Order->client ? $Order->client->ClientFullName() : '',
                            'phones' => $phones,
                        ],
                        'comments' => [
                            'has_waypoints_notes' => !empty($waypoints_notes),
                            'has_work_notes' => !empty($work_notes),
                            'waypoints_notes' => $waypoints_notes,
                            'work_notes' => $work_notes,
                        ],
                        'order' => $Order->only(['id', 'href', 'work']),
                        'manager' => isset($Order->manager->employee) ? $Order->manager->employee->toArray() : [],
                    ],
                ],
            ],
        ];

        if (isset($Order->manager)) {
            $body['Messages'][0]['ReplyTo'] = [
                'Email' => $Order->manager->email,
                'Name' => $Order->manager->name,
            ];
        }


        $response = $mj->post(Resources::$Email, ['body' => $body]);

        if ($response->success()) {
            $Order->addActivity('email', [
                'to' => $email->value,
                'text' => 'Notify work assign',
                'template_id' => 2637692,
                'employee_id' => $Employee->id,
                'work_id' => $Work->id,
                'events' => [],
                'ext_id' => $response->getData()['Messages'][0]['To'][0]['MessageID'],
            ]);

            $miscs = (array) $DispatchedEmployer->miscs;
            $miscs['notify'] = [
                'date' => now()->toDateTimeString(),
                'notify_id' => $response->getData()['Messages'][0]['To'][0]['MessageID'],
            ];
            $DispatchedEmployer->miscs = $miscs;
            $DispatchedEmployer->save(['touch' => false]);
            return $response->getData();
        }

        throw new \Exception("Send failed to \"{$email->value}\". Error with mail gateway, try again later");
    }

    /**
     * Send a notification for employee that they were assigned to the work.
     * @param  Request  $request
     * @return JsonResponse
     */
    public function sendNotify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'employee_id' => 'required|integer|exists:employees,id',
            'work_id' => 'required|integer|exists:orders_works,id',
        ]);

        $order = Order::with([
            'manager:id,name,email',
            'manager.employee',
            'works' => function ($q) use ($validated) {
                return $q
                    ->whereId($validated['work_id'])
                    ->with([
                        'workTypes:title,orders_works_2_work.*',
                        'dispatchEmployees' => function ($q) use ($validated) {
                            return $q->where('employer_id', $validated['employee_id']);
                        },
                    ]);
            },
        ])
            ->find($validated['order_id']);


        $f_work = $order->works->first();
        $dispatch_on_user = $f_work->dispatchEmployees->first();

        $order->work = [
            'start_date' => Carbon::parse($f_work->start_date)->format('m/d/Y'),
            'start_time' => $f_work->start_time_to ?
                Carbon::parse($f_work->start_time)->format('g:i A').' - '.Carbon::parse($f_work->start_time_to)->format('g:i A') :
                Carbon::parse($f_work->start_time)->format('g:i A'),
            'work_types' => $f_work->workTypes->implode('title', ', '),
        ];
        $order->href = route('customer.orderPublicView', ['hash' => $order->hash]);


        $employee = Employee::with('emails')->find($validated['employee_id']);

        $email = $employee->emails->first();
        if ($email) {
            $mj = new Client(config('app.mail_jet.public'), config('app.mail_jet.private'), true,
                ['version' => 'v3.1']);

            $body = [
                'Messages' => [
                    [
                        'To' => [
                            [
                                'Email' => $email->value,
                                'Name' => $employee->name,
                            ],
                        ],
                        'TemplateID' => 2637692,
                        'TemplateLanguage' => true,
                        'TemplateErrorReporting' => [
                            'Email' => 'webmaster.dobs@gmail.com',
                            'Name' => 'dobs',
                        ],
                        'Variables' => [
                            'ORDER' => [ // Нужен этот костыль верхним регистром т.к. без него не цепляло переменных
                                'ID' => 'TEST',
                            ],
                            'order' => $order->only(['id', 'href', 'work']),
                            'manager' => $order->manager && $order->manager->employee ? $order->manager->employee->toArray() : [],
                        ],
                    ],
                ],
            ];

            if (isset($order->manager)) {
                $body['Messages'][0]['ReplyTo'] = [
                    'Email' => $order->manager->email,
                    'Name' => $order->manager->name,
                ];
            }

            $response = $mj->post(Resources::$Email, ['body' => $body]);
            if ($response->success()) {
                $order->addActivity('email', [
                    'to' => $email->value,
                    'text' => 'Notify work assign',
                    'template_id' => 2637692,
                    'employee_id' => $validated['employee_id'],
                    'work_id' => $validated['work_id'],
                    'events' => [],
                    'ext_id' => $response->getData()['Messages'][0]['To'][0]['MessageID'],
                ]);

                $miscs = (array) $dispatch_on_user->miscs;
                $miscs['notify'] = [
                    'date' => now()->toDateTimeString(),
                    'notify_id' => $response->getData()['Messages'][0]['To'][0]['MessageID'],
                ];
                $dispatch_on_user->miscs = $miscs;
                $dispatch_on_user->save(['touch' => false]);

                return response()
                    ->json([
                        'success' => true,
                        'msg' => 'Message successfully sent',
                        'data' => $response->getData(),
                    ]);
            }

            return response()
                ->json([
                    'success' => false,
                    'msg' => 'Error on mail gateway, try again later',
                    'data' => $response->getData(),
                ]);
        } else {
            return response()
                ->json([
                    'success' => false,
                    'msg' => 'Employee account without email addresses. Fill in address!',
                ]);
        }
    }

    /**
     * Get the date of the last saving.
     * To display an error if someone else made edits.
     * @param  string  $date
     * @return mixed
     */
    private function getLatestUpdatedWork(string $date)
    {
        return Order\Work::where('start_date', $date)->withTrashed()->max('dispatch_updated_at');
    }
}
