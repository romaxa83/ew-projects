<?php

namespace App\Filters\Calls;

use App\Enums\Formats\DatetimeEnum;
use App\Models\Calls\History;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use App\Traits\Filter\StatusFilterTrait;
use Carbon\Carbon;
use EloquentFilter\ModelFilter;

/**
 * @mixin History
 */
class HistoryFilter extends ModelFilter
{
    use IdFilterTrait;
    use SortFilterTrait;
    use StatusFilterTrait;

    protected function allowedOrders(): array
    {
        return History::ALLOWED_SORTING_FIELDS;
    }

    public function department(string|int $value): void
    {
        $this->where('department_id', $value);
    }

    public function employee(string|int $value): void
    {
        $this->whereRaw("(employee_id=? OR from_employee_id=?)", [$value, $value]);
    }

    public function serialNumber(string $value): void
    {
        $this->where('serial_numbers', 'like', '%'.$value.'%');
    }

    public function case(string $value): void
    {
        $this->where('case_id', 'like', '%'.$value.'%');
    }

    public function search(string $value): void
    {
        $this->whereRaw("(from_num LIKE '%{$value}%' OR from_name_pretty LIKE '%{$value}%' OR dialed LIKE '%{$value}%' OR dialed_name LIKE '%{$value}%' OR comment LIKE '%{$value}%')")
        ;

//        $this->whereRaw("from_num LIKE '%{$value}%'")
//            ->orWhereRaw("from_name LIKE '%{$value}%'")
//            ->orWhereHas('employee', function($q) use ($value){
//                return $q->whereRaw("(first_name LIKE '{$value}%' OR last_name LIKE '%{$value}%')")
//                    ->orWhereHas('sip', function($q) use($value) {
//                        return $q->whereRaw("number LIKE '%{$value}%'");
//                    })
//                    ;
//            })
//        ;
    }

    public function dateFrom(string $from): void
    {
        $fromDate = Carbon::createFromFormat(DatetimeEnum::DEFAULT_FORMAT, $from);

        $this->where('call_date', '>=', $fromDate);
    }

    public function dateTo(string $to): void
    {
        $toDate = Carbon::createFromFormat(DatetimeEnum::DEFAULT_FORMAT, $to);

        $this->where('call_date', '<=', $toDate);
    }
}
