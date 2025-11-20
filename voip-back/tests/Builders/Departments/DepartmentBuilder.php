<?php

namespace Tests\Builders\Departments;

use App\Models\Departments\Department;
use Tests\Builders\BaseBuilder;

class DepartmentBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Department::class;
    }
}
