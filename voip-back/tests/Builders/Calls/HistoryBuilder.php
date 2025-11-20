<?php

namespace Tests\Builders\Calls;

use App\Enums\Calls\HistoryStatus;
use App\Models\Calls\History;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use Tests\Builders\BaseBuilder;

class HistoryBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return History::class;
    }

    public function setEmployee(Employee $model): self
    {
        $this->data['employee_id'] = $model->id;

        return $this;
    }

    public function setFromEmployee(Employee $model): self
    {
        $this->data['from_employee_id'] = $model->id;

        return $this;
    }

    public function setDepartment(Department $model): self
    {
        $this->data['department_id'] = $model->id;

        return $this;
    }

    public function setChannel(string $value): self
    {
        $this->data['channel'] = $value;

        return $this;
    }

    public function setStatus(HistoryStatus $value): self
    {
        $this->data['status'] = $value;

        return $this;
    }
}

