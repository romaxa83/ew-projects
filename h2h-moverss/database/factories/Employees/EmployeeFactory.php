<?php

namespace Database\Factories\Employees;

use App\Models\Employee;
use App\User;
use Database\Factories\BaseFactory;

class EmployeeFactory extends BaseFactory
{
    protected $model = Employee::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'active' => 1,
            'division_ids' => [1],
            'name' => $this->faker->name(),
            'l_name' => $this->faker->lastName(),
            'address' => $this->faker->address(),
            'birthday' => null,
            'pay_type' => null,
            'driver_start_of_work' => null,
            'driver_notes' => null,
            'signature' => null,
            'pbx_ext' => null,
            'pbx_show_webrtc' => 0,
            'auth_user_id' => User::factory(),
            'partner_id' => null,
            'deleted_at' => null,
            'ringostat_sip_status' => false,
            'ringostat_id' => null,
            'ringostat_call_rec_id' => null,
            'ringostat_miscs' => null,
            'sales_team' => null,
        ];
    }
}
