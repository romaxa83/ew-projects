<?php

namespace App\Repositories\Departments;

use App\Models\Departments\Department;
use App\Repositories\AbstractRepository;
use Illuminate\Support\Facades\DB;

final class DepartmentRepository extends AbstractRepository
{
    public function modelClass(): string
    {
        return Department::class;
    }
    public function getDepartmentIdsAndName(): array
    {
        return DB::table(Department::TABLE)
            ->get()
            ->pluck('id','name')
            ->toArray();
    }

    public function getDepartmentIdsAndUuid(): array
    {
        return DB::table(Department::TABLE)
            ->get()
            ->pluck('id','guid')
            ->toArray();
    }
}
