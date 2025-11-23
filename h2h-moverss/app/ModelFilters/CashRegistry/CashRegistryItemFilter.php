<?php

namespace App\ModelFilters\CashRegistry;

use App\Enums\Common\DateFormat;
use App\ModelFilters\BaseModelFilter;
use App\Models\CashRegistry\CashRegistryItem;
use App\Models\Employee;
use Carbon\CarbonImmutable;

/**
 * @mixin CashRegistryItem
*/
class CashRegistryItemFilter extends BaseModelFilter
{
    const DATE_TIME_REG = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';

    public function type(string $value): void
    {
        $this->where('type', $value);
    }

    public function startDate(string $value): void
    {
        if(preg_match(self::DATE_TIME_REG, $value)){
            $date = $value;
        } else {
            $date = CarbonImmutable::createFromFormat(DateFormat::FILTER_DATE(), $value)
                ->startOfDay();
        }

        $this->where('insert_date', '>=', $date);
    }

    public function endDate(string $value): void
    {
        if(preg_match(self::DATE_TIME_REG, $value)){
            $date = $value;
        } else {
            $date = CarbonImmutable::createFromFormat(DateFormat::FILTER_DATE(), $value)
                ->endOfDay();
        }

        $this->where('insert_date', '<=', $date);
    }

    public function employee(string|int $value): void
    {
        $this->whereHas('foreman', function ($query) use ($value) {
            $query->where(Employee::TABLE. '.id', $value);
        });
    }

}
