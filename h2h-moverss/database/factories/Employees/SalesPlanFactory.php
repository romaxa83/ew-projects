<?php

namespace Database\Factories\Employees;

use App\Models\Employee;
use Carbon\CarbonImmutable;
use Database\Factories\BaseFactory;

class SalesPlanFactory extends BaseFactory
{
    protected $model = Employee\SalesPlan::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'local' => random_int(1, 50),
            'intrestate' => random_int(51, 99),
            'date_at' => CarbonImmutable::now(),
        ];
    }
}
