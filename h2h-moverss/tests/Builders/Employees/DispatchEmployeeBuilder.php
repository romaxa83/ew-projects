<?php

namespace Tests\Builders\Employees;

use App\Models\DispatchEmployer;
use App\Models\Employee;
use App\Models\Order\Work;
use Tests\Builders\BaseBuilder;

class DispatchEmployeeBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return DispatchEmployer::class;
    }

    public function employee(Employee $model): self
    {
        $this->data['employee_id'] = $model->id;
        return $this;
    }

    public function work(Work $model): self
    {
        $this->data['work_id'] = $model->id;
        return $this;
    }
}

