<?php

namespace Database\Factories\Calls;

use App\Enums\Calls\HistoryStatus;
use App\Models\Calls\History;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use Carbon\CarbonImmutable;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|History[]|History create(array $attributes = [])
 */
class HistoryFactory extends BaseFactory
{
    protected $model = History::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'from_employee_id' => null,
            'department_id' => Department::factory(),
            'status' => HistoryStatus::ANSWERED,
            'from_num' => $this->faker->phoneNumber,
            'from_name' => $this->faker->name,
            'from_name_pretty' => $this->faker->name,
            'dialed' => $this->faker->phoneNumber,
            'dialed_name' => $this->faker->name,
            'duration' => 44,
            'billsec' => 44,
            'serial_numbers' => $this->faker->uuid,
            'case_id' => $this->faker->postcode,
            'comment' => $this->faker->sentence,
            'lastapp' => 'Dial',
            'uniqueid' => $this->faker->uuid,
            'channel' => $this->faker->uuid,
            'call_date' => CarbonImmutable::now(),
        ];
    }
}
