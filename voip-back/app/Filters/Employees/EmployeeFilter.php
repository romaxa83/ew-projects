<?php

namespace App\Filters\Employees;

use App\Models\Employees\Employee;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\StatusFilterTrait;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Employee
 */
class EmployeeFilter extends ModelFilter
{
    use IdFilterTrait;
    use StatusFilterTrait;

    public function department(string $value): void
    {
        $this->where('department_id', $value);
    }

    public function sip(string $value): void
    {
        $this->where('sip_id', $value);
    }

    public function search(string $value): void
    {
        $value = explode(' ', trim($value));

        $this->where(function (Builder $q) use ($value) {
            if(count($value) >= 2){
                return $q->whereRaw("(first_name LIKE '{$value[0]}%' OR last_name LIKE '%{$value[1]}%')");
            }

            return $q->whereRaw("(first_name LIKE '{$value[0]}%' OR last_name LIKE '%{$value[0]}%')")
                ->orWhereRaw("email LIKE '%{$value[0]}%'")
                ;
        });
    }

    public function hasSip(bool $value)
    {
        if($value){
            $this->has('sip');
        } else {
            $this->doesntHave('sip');
        }
    }

    public function hasActiveDepartment(bool $value): void
    {
        $this->whereHas('department', fn(Builder $b) => $b->where('active', $value));
    }
}
