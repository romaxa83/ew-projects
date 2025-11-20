<?php

namespace Tests\Builders\Reports;

use App\Models\Employees\Employee;
use App\Models\Reports\Report;
use Tests\Builders\BaseBuilder;

class ReportBuilder extends BaseBuilder
{
    public function modelClass(): string
    {
        return Report::class;
    }

    public function setEmployee(Employee $model): self
    {
        $this->data['employee_id'] = $model->id;
        return $this;
    }
}
