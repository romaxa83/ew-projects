<?php

namespace App\Filters\Departments;

use App\Models\Departments\Department;
use App\Traits\Filter\ActiveFilter;
use App\Traits\Filter\IdFilterTrait;
use EloquentFilter\ModelFilter;

/**
 * @mixin Department
 */
class DepartmentFilter extends ModelFilter
{
    use IdFilterTrait;
    use ActiveFilter;
}
