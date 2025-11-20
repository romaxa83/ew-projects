<?php

namespace Database\Factories\Schedules;

use App\Enums\Formats\DayEnum;
use App\Models\Schedules\Schedule;
use App\Models\Schedules\WorkDay;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|WorkDay[]|WorkDay create(array $attributes = [])
 */
class WorkDayFactory extends BaseFactory
{
    protected $model = WorkDay::class;

    public function definition(): array
    {
        return [
            'schedule_id' => Schedule::factory(),
            'name' => DayEnum::MONDAY(),
            'start_work_time' => '6:00',
            'end_work_time' => '17:00',
            'active' => true,
            'sort' => 1,
        ];
    }
}
