<?php

namespace App\Events\Departments;

use App\Models\Departments\Department;

class DepartmentCreatedEvent
{
    public function __construct(
        protected Department $model
    )
    {}

    public function getModel(): Department
    {
        return $this->model;
    }
}
