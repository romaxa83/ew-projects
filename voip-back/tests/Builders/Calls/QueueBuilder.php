<?php

namespace Tests\Builders\Calls;

use App\Enums\Calls\QueueStatus;
use App\Enums\Calls\QueueType;
use App\Models\Calls\Queue;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class QueueBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Queue::class;
    }

    function setStatus(QueueStatus $value): self
    {
        $this->data['status'] = $value;
        return $this;
    }

    function setConnectionNum(string $value): self
    {
        $this->data['connected_num'] = $value;
        return $this;
    }

    function setConnectionName(string $value): self
    {
        $this->data['connected_name'] = $value;
        return $this;
    }

    function setFromName(string $value): self
    {
        $this->data['caller_name'] = $value;
        return $this;
    }

    function setFromNum(string $value): self
    {
        $this->data['caller_num'] = $value;
        return $this;
    }

    function setType(QueueType $value): self
    {
        $this->data['type'] = $value;
        return $this;
    }

    function setConnectedAt(CarbonImmutable $value): self
    {
        $this->data['connected_at'] = $value;
        return $this;
    }

    function setInCall(int $value): self
    {
        $this->data['in_call'] = $value;
        return $this;
    }

    function setCalledAt(CarbonImmutable $value): self
    {
        $this->data['called_at'] = $value;
        return $this;
    }

    function setChannel(string $value): self
    {
        $this->data['channel'] = $value;
        return $this;
    }

    function setUniqueid(string $value): self
    {
        $this->data['uniqueid'] = $value;
        return $this;
    }

    function setPosition(int $value): self
    {
        $this->data['position'] = $value;
        return $this;
    }

    function setWait(int $value): self
    {
        $this->data['wait'] = $value;
        return $this;
    }

    public function setDepartment(Department $model): self
    {
        $this->data['department_id'] = $model->id;

        return $this;
    }

    public function setEmployee(Employee $model): self
    {
        $this->data['employee_id'] = $model->id;

        return $this;
    }

    public function setComment(?string $value): self
    {
        $this->data['comment'] = $value;

        return $this;
    }

    public function setCaseId(string $value): self
    {
        $this->data['case_id'] = $value;

        return $this;
    }

    public function setSerialNumber(string $value): self
    {
        $this->data['serial_number'] = $value;

        return $this;
    }
}
