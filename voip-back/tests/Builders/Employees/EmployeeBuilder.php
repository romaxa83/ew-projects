<?php

namespace Tests\Builders\Employees;

use App\Enums\Employees\Status;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use App\Models\Sips\Sip;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class EmployeeBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Employee::class;
    }

    public function setSip(Sip $model): self
    {
        $this->data['sip_id'] = $model->id;

        return $this;
    }

    public function setDepartment(Department $model): self
    {
        $this->data['department_id'] = $model->id;

        return $this;
    }

    public function setStatus(Status $value): self
    {
        $this->data['status'] = $value;

        return $this;
    }

    public function isDeleted(): self
    {
        $this->data['deleted_at'] = CarbonImmutable::now();

        return $this;
    }
}
