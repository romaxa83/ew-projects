<?php

namespace Database\Seeders;

use App\Enums\Formats\DayEnum;
use App\Models\Schedules\Schedule;
use App\Models\Schedules\WorkDay;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        if(Schedule::all()->isEmpty()){
            $this->create();
        }

    }

    protected function create()
    {
        $model = new Schedule();
        $model->save();

        foreach ($this->data() as $item){
            $day = new WorkDay();
            $day->schedule_id = $model->id;
            $day->name = data_get($item, 'name');
            $day->sort = data_get($item, 'sort');
            $day->start_work_time = data_get($item, 'start_work_time');
            $day->end_work_time = data_get($item, 'end_work_time');
            $day->save();
        }
    }

    protected function data(): array
    {
        return [
            [
                'name' => DayEnum::SUNDAY(),
                'sort' => 1
            ],
            [
                'name' => DayEnum::MONDAY(),
                'sort' => 2,
                'start_work_time' => '8:00',
                'end_work_time' => '17:00',
            ],
            [
                'name' => DayEnum::TUESDAY(),
                'sort' => 3,
                'start_work_time' => '8:00',
                'end_work_time' => '17:00',
            ],
            [
                'name' => DayEnum::WEDNESDAY(),
                'sort' => 4,
                'start_work_time' => '8:00',
                'end_work_time' => '17:00',
            ],
            [
                'name' => DayEnum::THURSDAY(),
                'sort' => 5,
                'start_work_time' => '8:00',
                'end_work_time' => '17:00',
            ],
            [
                'name' => DayEnum::FRIDAY(),
                'sort' => 6,
                'start_work_time' => '8:00',
                'end_work_time' => '17:00',
            ],
            [
                'name' => DayEnum::SATURDAY(),
                'sort' => 7,
            ]
        ];
    }
}

