<?php

namespace Database\Factories\AA;

use App\Models\AA\AAOrderPlanning;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class AAOrderPlanningFactory extends Factory
{
    protected $model = AAOrderPlanning::class;

    public function definition(): array
    {
        return [
            'post_uuid' => $this->faker->uuid,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()
        ];
    }
}


