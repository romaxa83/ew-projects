<?php

namespace Tests\Builders\Asterisk;

use App\IPTelephony\Services\Storage\Asterisk\QueueMemberService;
use App\Models\Employees\Employee;

class QueueMemberBuilder
{
    protected Employee $employee;

    public function __construct(protected QueueMemberService $service)
    {}

    public function setEmployee(Employee $model): self
    {
        $this->employee = $model;
        return $this;
    }

    function create(): object
    {
        $this->service->create($this->employee);

        return $this->service->getBy('uuid', $this->employee->guid);
    }

}

