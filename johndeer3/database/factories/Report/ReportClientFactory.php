<?php

namespace Database\Factories\Report;

use App\Models\Report\ReportClient;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportClientFactory extends Factory
{
    protected $model = ReportClient::class;

    public function definition(): array
    {
        return [
            'customer_id' => $this->faker->uuid,
            'company_name' => $this->faker->company,
            'customer_first_name' => $this->faker->firstName,
            'customer_last_name' => $this->faker->lastName,
            'phone' => $this->faker->phoneNumber,
            'status' => true,
            'comment' => $this->faker->sentence,
        ];
    }
}


