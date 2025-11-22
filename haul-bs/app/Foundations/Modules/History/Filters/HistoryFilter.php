<?php

namespace App\Foundations\Modules\History\Filters;

use App\Foundations\Models\BaseModelFilter;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class HistoryFilter extends BaseModelFilter
{
    public function user(int|string $value): self
    {
        return $this->where('user_id', $value);
    }

    public function datesRange(string $datesRange)
    {
        $range = explode(' - ', $datesRange);
        $startDate = CarbonImmutable::parse($range[0])->startOfDay();
        $endDate = isset($range[1]) ? CarbonImmutable::parse($range[1])->endOfDay() : CarbonImmutable::parse($range[0])->endOfDay();

        return $this->where(function (Builder $query) use ($startDate, $endDate) {
            return $query->where('performed_at', '>=', $startDate)
                ->where('performed_at', '<=', $endDate);
        });
    }
}
