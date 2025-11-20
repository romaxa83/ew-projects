<?php

namespace App\Services\Schedules;

use App\Dto\Schedules\ScheduleDto;
use App\Models\Musics\Music;
use App\Models\Schedules\AdditionsDay;
use App\Models\Schedules\Schedule;
use App\Models\Schedules\WorkDay;
use App\Repositories\Schedules\ScheduleRepository;
use App\Services\AbstractService;
use App\Services\Musics\MusicService;
use Carbon\CarbonImmutable;

class ScheduleService extends AbstractService
{
    public function __construct()
    {
        $this->repo = resolve(ScheduleRepository::class);
        return parent::__construct();
    }

    public function update(Schedule $model, ScheduleDto $dto): Schedule
    {
        foreach ($dto->days as $day){
            if($m = $model->days->where('id', $day->id)->first()){
                /** @var $m WorkDay */
                $m->start_work_time = $day->startWorkTime;
                $m->end_work_time = $day->endWorkTime;
                $m->active = $day->active;

                if($m->name == current_day()){
                    $now = dateByTz(CarbonImmutable::now());
                    $oldEndWorkTime = CarbonImmutable::createFromTimeString($m->getOriginal('end_work_time'));
                    $newEndWorkTime = CarbonImmutable::createFromTimeString($day->endWorkTime);

                    logger_info('UNHOLD MUSIC FROM UPDATE SCHEDULE', [
                        'CURRENT_DAY' => current_day(),
                        'NOW' => $now,
                        'OLD_END_WORK_TIME' => $oldEndWorkTime,
                        'NEW_END_WORK_TIME' => $newEndWorkTime,
                        'eq1' => $newEndWorkTime != $oldEndWorkTime,
                        'eq2' => $now->addMinutes(config('asterisk.music.hold_to_end_work_day')) <= $newEndWorkTime,
                        'eq3' => Music::query()->where('has_unhold_data', true)->exists(),
                        '1' => $now->addMinutes(config('asterisk.music.hold_to_end_work_day')),
                    ]);

                    if(
                        $newEndWorkTime != $oldEndWorkTime
                        && $now->addMinutes(config('asterisk.music.hold_to_end_work_day')) <= $newEndWorkTime
                        && Music::query()->where('has_unhold_data', true)->exists()
                    ){
                        MusicService::unholdMusic();
                    }
                }

                $m->save();
            }
        }

        $model->additionalDays()->delete();

        foreach ($dto->additionDays as $additionDay){
            /** @var $a AdditionsDay */
            $a = new AdditionsDay();
            $a->start_at = $additionDay->startAt;
            $a->end_at = $additionDay->endAt;
            $a->schedule_id = $model->id;
            $a->save();
        }

         return $model;
    }
}
