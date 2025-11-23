<?php

namespace Database\Factories\Employees;

use App\Models\DispatchEmployer;
use App\Models\Employee;
use App\Models\Order\Work;
use Database\Factories\BaseFactory;

class DispatchEmployeeFactory extends BaseFactory
{
    protected $model = DispatchEmployer::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'work_id' => Work::factory(),
            'employer_id' => Employee::factory(),
            'miscs' => [],
        ];
    }
}


