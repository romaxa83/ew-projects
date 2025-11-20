<?php

namespace App\IPTelephony\Services\Storage\Asterisk;

use App\IPTelephony\Entities\Asterisk\WorkTimeEntity;
use App\Models\Schedules\Schedule;
use Carbon\CarbonImmutable;

class WorkTimeService extends AsteriskService
{
    public function getTable(): string
    {
        return WorkTimeEntity::TABLE;
    }

    public function setIsWorkTime(): void
    {
        /** @var $model Schedule */
        $model = Schedule::query()->with(['days', 'additionalDays'])->first();
        $now = CarbonImmutable::now();

        if($model){
            // разбираем праздничные дни
            if($model->additionalDays->isNotEmpty()){
                // если сегодня праздничный день, проставляем как не рабочий
                if(
                    $model->additionalDays()
                    ->whereDay('start_at', $now)
                    ->exists()
                ){
                    $this->insert([
                        'date' => $now,
                        'work_status' => 0,
                    ]);
                    return ;
                }
                // если сегодня находиться в периоде праздничных дней, проставляем как не рабочий
                if(
                    $model->additionalDays()
                    ->where('start_at', '<=', $now)
                    ->where('end_at', '>=', $now)
                    ->exists()
                ){
                    $this->insert([
                        'date' => $now,
                        'work_status' => 0,
                    ]);
                    return ;
                }

            }

            if($day = $model->days->where('name', strtolower($now->isoFormat('dddd')))->first()){
//                dd($day->start_work_time === null);
                // если не указано начала\конец раб.дня, или он не активен проставляем как не рабочий
                if(
                    $day->start_work_time === null
                    || $day->end_work_time === null
                    || !$day->active
                ){
                    $this->insert([
                        'date' => $now,
                        'work_status' => 0,
                    ]);
                    return ;
                } else {
                    $timeStart = explode(':', $day->start_work_time);
                    $dayStart = CarbonImmutable::create(
                        $now->year,
                        $now->month,
                        $now->day,
                        $timeStart[0],
                        $timeStart[1],
                    );

                    $timeEnd = explode(':', $day->end_work_time);
                    $dayEnd = CarbonImmutable::create(
                        $now->year,
                        $now->month,
                        $now->day,
                        $timeEnd[0],
                        $timeEnd[1],
                    );

                    if(substr(config('app.current_offset_to_utc'), 0, 1) == '-'){
                        $nowOffice = $now->subHours(substr(config('app.current_offset_to_utc'), 1));
                    } else {
                        $nowOffice = $now->addHours(config('app.current_offset_to_utc'));
                    }

                    if($nowOffice->between($dayStart, $dayEnd)){
                        $this->insert([
                            'date' => $now,
                            'work_status' => 1,
                        ]);
                    } else {
                        $this->insert([
                            'date' => $now,
                            'work_status' => 0,
                        ]);
                    }
                }
            }
        }
    }
}
