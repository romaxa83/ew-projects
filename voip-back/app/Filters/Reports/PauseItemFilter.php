<?php

namespace App\Filters\Reports;

use App\Enums\Formats\DatetimeEnum;
use App\Models\Reports\PauseItem;
use App\Traits\Filter\IdFilterTrait;
use Carbon\Carbon;
use EloquentFilter\ModelFilter;

/**
 * @mixin PauseItem
 */
class PauseItemFilter extends ModelFilter
{
    use IdFilterTrait;

    public function report(string|int $value): void
    {
        $this->where('report_id', $value);
    }

    public function dateFrom(string $from): void
    {
        $fromDate = Carbon::createFromFormat(DatetimeEnum::DEFAULT_FORMAT, $from);

        $this->where('pause_at', '>=', $fromDate);
    }

    public function dateTo(string $to): void
    {
        $toDate = Carbon::createFromFormat(DatetimeEnum::DEFAULT_FORMAT, $to);

        $this->where('unpause_at', '<=', $toDate);
    }
}
