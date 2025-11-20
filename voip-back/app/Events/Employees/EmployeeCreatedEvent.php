<?php

namespace App\Events\Employees;

use App\Dto\Employees\EmployeeDto;
use App\Models\Employees\Employee;

class EmployeeCreatedEvent
{
    public function __construct(
        protected Employee $model,
        protected ?EmployeeDto $dto = null,
    )
    {}

    public function getModel(): Employee
    {
        return $this->model;
    }

    public function getDto(): ?EmployeeDto
    {
        return $this->dto;
    }
}
