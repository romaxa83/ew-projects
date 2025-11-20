<?php

namespace Database\Factories\Schedules;

use App\Models\Schedules\Schedule;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Schedule[]|Schedule create(array $attributes = [])
 */
class ScheduleFactory extends BaseFactory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        return [];
    }
}
