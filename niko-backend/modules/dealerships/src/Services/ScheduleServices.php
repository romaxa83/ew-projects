<?php

namespace WezomCms\Dealerships\Services;

use WezomCms\Dealerships\Models\Dealership;
use WezomCms\Dealerships\Models\Schedule;

class ScheduleServices
{
    public function createOrUpdate(array $data, Dealership $model)
    {
        foreach ($data as $type => $items){
            foreach ($items as $day => $item){
                /** @var $daySchedule Schedule*/
                $daySchedule = $type == Schedule::TYPE_SERVICE
                    ? $model->getScheduleServiceDay($day)
                    : $model->getScheduleSalonDay($day);

                if($daySchedule){
                    $daySchedule->work_start = $item['work_start'];
                    $daySchedule->work_end = $item['work_end'];
                    $daySchedule->break_start = $item['break_start'];
                    $daySchedule->break_end = $item['break_end'];
                    $daySchedule->save();
                } else {
                    $schedule = new Schedule();
                    $schedule->dealership_id = $model->id;
                    $schedule->day = $day;
                    $schedule->work_start = $item['work_start'];
                    $schedule->work_end = $item['work_end'];
                    $schedule->break_start = $item['break_start'];
                    $schedule->break_end = $item['break_end'];
                    $schedule->type = $type;
                    $schedule->save();
                }
            }
        }
    }
}
