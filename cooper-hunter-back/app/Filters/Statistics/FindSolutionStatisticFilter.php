<?php

namespace App\Filters\Statistics;

use App\Enums\Formats\DatetimeEnum;
use App\Filters\BaseModelFilter;
use App\Models\Statistics\FindSolutionStatistic;
use Carbon\Carbon;

/**
 * @mixin FindSolutionStatistic
 */
class FindSolutionStatisticFilter extends BaseModelFilter
{
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