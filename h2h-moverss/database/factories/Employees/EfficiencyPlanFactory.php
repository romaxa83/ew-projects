<?php

namespace Database\Factories\Employees;

use App\Models\Employee;
use Carbon\CarbonImmutable;
use Database\Factories\BaseFactory;

class EfficiencyPlanFactory extends BaseFactory
{
    protected $model = Employee\EfficiencyPlan::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'conversion_local_team' => null,
            'conversion_long_team' => null,
            'date_at' => CarbonImmutable::now(),
        ];
    }
}
