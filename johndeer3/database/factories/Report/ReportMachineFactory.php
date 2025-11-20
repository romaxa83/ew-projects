<?php

namespace Database\Factories\Report;

use App\Models\Report\ReportMachine;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportMachineFactory extends Factory
{
    protected $model = ReportMachine::class;

    public function definition(): array
    {
        return [
            'serial_number_header' => $this->faker->creditCardNumber,
            'machine_serial_number' => $this->faker->creditCardNumber,
            'sub_machine_serial_number' => $this->faker->creditCardNumber,
            'trailer_model' => $this->faker->creditCardNumber,
            'trailed_equipment_type' => $this->faker->creditCardNumber,
        ];
    }
}
