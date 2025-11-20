<?php

namespace App\Filters\Reports;

use App\Models\Reports\Report;
use App\Traits\Filter\IdFilterTrait;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Report
 */
class ReportFilter extends ModelFilter
{
    use IdFilterTrait;

    public function department(string|int $value): void
    {
        $this->whereHas('employee', fn(Builder $b) => $b->where('department_id', $value));
    }

    public function employee(string|int $value): void
    {
        $this->where('employee_id', $value);
    }

    public function search(string $value): void
    {
        if(is_numeric($value)){

            $this->whereHas('employee', function(Builder $b) use($value){
                $b->whereHas('sip', function(Builder $b) use($value) {
                    $b->where('number', 'like', '%'.$value.'%');
                });
            });

        } else {
            $value = explode(' ', trim($value));

            $this->whereHas('employee', function (Builder $b) use ($value){
                if(count($value) >= 2){
                    return $b->whereRaw("(first_name LIKE '{$value[0]}%' OR last_name LIKE '%{$value[1]}%')");
                }

                return $b->whereRaw("(first_name LIKE '{$value[0]}%' OR last_name LIKE '%{$value[0]}%')");
            });
        }
    }

    public function hasActiveDepartment(bool $value): void
    {
        $this->whereHas('employeeWithTrashed',
            fn(Builder $b) => $b->whereHas('department',
                fn(Builder $b) => $b->where('active', $value)
            )
        );
    }
}
