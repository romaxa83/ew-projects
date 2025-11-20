<?php

namespace Database\Factories\Report;

use App\Models\Report\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        return [
            'status' => \App\Type\ReportStatus::CREATED,
            'salesman_name' => $this->faker->firstName,
            'assignment' => $this->faker->city,
            'result' => $this->faker->city,
            'client_comment' => $this->faker->city,
            'client_email' => $this->faker->city,
        ];
    }
}

