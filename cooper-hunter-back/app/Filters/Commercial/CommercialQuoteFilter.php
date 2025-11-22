<?php

namespace App\Filters\Commercial;

use App\Enums\Formats\DatetimeEnum;
use App\Filters\BaseModelFilter;
use App\Traits\Filter\IdFilterTrait;
use Carbon\Carbon;

/**
 * @mixin CommercialQuoteFilter
 */
class CommercialQuoteFilter extends BaseModelFilter
{
    use IdFilterTrait;

    public function status(string $status): void
    {
        $this->where('status', $status);
    }

    public function projectName(string $value): void
    {
        $this->whereHas('commercialProject', function($q) use ($value){
            return $q->where('name', 'like', $value . '%');
        });
    }

    public function technicianName(string $value): void
    {
        $value = explode(' ', trim($value));

        $this->whereHas('commercialProject', function($q) use ($value){
            return $q->whereHas('member', function($q) use ($value){
                if(count($value) >= 2){
                    return $q->whereRaw("(first_name LIKE '{$value[0]}%' OR last_name LIKE '%{$value[1]}%')");
                }
                return $q->whereRaw("(first_name LIKE '{$value[0]}%' OR last_name LIKE '%{$value[0]}%')");
            });
        });
    }

    public function dateFrom(string $from): void
    {
        $fromDate = Carbon::createFromFormat(DatetimeEnum::DATE, $from);

        $this->where('created_at', '>=', $fromDate->startOfDay());
    }

    public function dateTo(string $to): void
    {
        $toDate = Carbon::createFromFormat(DatetimeEnum::DATE, $to);

        $this->where('created_at', '<=', $toDate->endOfDay());
    }
}
