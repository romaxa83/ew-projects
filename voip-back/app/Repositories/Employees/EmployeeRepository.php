<?php

namespace App\Repositories\Employees;

use App\Models\Employees\Employee;
use App\Models\Sips\Sip;
use App\Repositories\AbstractRepository;
use Illuminate\Support\Facades\DB;

final class EmployeeRepository extends AbstractRepository
{
    public function modelClass(): string
    {
        return Employee::class;
    }

    public function getEmployeeIdAdnSipNumber(): array
    {
        return DB::table(Employee::TABLE)
            ->select(Employee::TABLE. '.id as employee_id', Sip::TABLE. '.number as sip_number')
            ->join(Sip::TABLE, Employee::TABLE.'.sip_id', '=', Sip::TABLE.'.id')
            ->get()
            ->pluck('employee_id','sip_number')
            ->toArray();
    }

    public function getEmployeeDataBySips()
    {
        return DB::table(Employee::TABLE)
            ->select(
                Employee::TABLE. '.id as employee_id',
                Employee::TABLE. '.department_id',
                Sip::TABLE. '.number as sip_number'
            )
            ->join(Sip::TABLE, Employee::TABLE.'.sip_id', '=', Sip::TABLE.'.id')
            ->get()
            ->keyBy('sip_number')
            ->toArray()
            ;
    }

    public function getEmployeeGuidAdnSipNumber(): array
    {
        return DB::table(Employee::TABLE)
            ->select(Employee::TABLE. '.id as employee_id', Employee::TABLE. '.guid as employee_guid', Sip::TABLE. '.number as sip_number')
            ->join(Sip::TABLE, Employee::TABLE.'.sip_id', '=', Sip::TABLE.'.id')
            ->get()
            ->pluck('sip_number','employee_guid')
            ->toArray();
    }

    public function getEmployeeIdsAndUuid(): array
    {
        return DB::table(Employee::TABLE)
            ->get()
            ->pluck('id','guid')
            ->toArray();
    }
}
