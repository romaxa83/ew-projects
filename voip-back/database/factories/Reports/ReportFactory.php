<?php

namespace Database\Factories\Reports;

use App\Models\Employees\Employee;
use App\Models\Reports\Report;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Report[]|Report create(array $attributes = [])
 */
class ReportFactory extends BaseFactory
{
    protected $model = Report::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
        ];
    }
}
