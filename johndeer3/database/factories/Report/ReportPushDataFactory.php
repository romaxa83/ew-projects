<?php

namespace Database\Factories\Report;

use App\Models\Report\ReportPushData;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportPushDataFactory extends Factory
{
    protected $model = ReportPushData::class;

    public function definition(): array
    {
        return [
            'prev_planned_at' => null,
            'is_send_start_day' => false,
            'is_send_end_day' => false,
            'is_send_week' => false,
        ];
    }
}

