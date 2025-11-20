<?php

namespace App\Events\Employees;

use App\Models\Employees\Employee;

class EmployeeUpdatedEvent
{
    public function __construct(
        protected Employee $model
    )
    {}

    public function getModel(): Employee
    {
        return $this->model;
    }
}
