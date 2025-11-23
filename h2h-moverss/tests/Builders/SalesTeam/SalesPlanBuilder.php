<?php

namespace Tests\Builders\SalesTeam;

use App\Models\Employee;
use App\Models\Employee\EfficiencyPlan;
use App\Models\Employee\SalesPlan;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class SalesPlanBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return SalesPlan::class;
    }

    public function employee(Employee $model): self
    {
        $this->data['employee_id'] = $model->id;
        return $this;
    }

    public function date(string|CarbonImmutable $value): self
    {
        if($value instanceof CarbonImmutable) {
            $value = $value->format('Y-m');
        }

        $this->data['date_at'] = EfficiencyPlan::validDate($value);

        return $this;
    }

    public function local(?int $value): self
    {
        $this->data['local'] = $value;
        return $this;
    }

    public function intrestate(int $value): self
    {
        $this->data['intrestate'] = $value;
        return $this;
    }
}

