<?php

namespace Database\Factories\Schedules;

use App\Models\Schedules\AdditionsDay;
use App\Models\Schedules\Schedule;
use Carbon\Carbon;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|AdditionsDay[]|AdditionsDay create(array $attributes = [])
 */
class AdditionsDayFactory extends BaseFactory
{
    protected $model = AdditionsDay::class;

    public function definition(): array
    {
        return [
            'schedule_id' => Schedule::factory(),
            'start_at' => Carbon::now(),
            'end_at' => null,
        ];
    }
}
