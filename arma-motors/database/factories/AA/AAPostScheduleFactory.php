<?php

namespace Database\Factories\AA;

use App\Models\AA\AAPostSchedule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class AAPostScheduleFactory extends Factory
{
    protected $model = AAPostSchedule::class;

    public function definition(): array
    {
        $date = Carbon::today();

        return [
            'date' => $date,
            'start_work' => $date->addHours(8),
            'end_work' => $date->addHours(20),
            'work_day' => true
        ];
    }
}
