<?php

namespace App\Filters\Reports;

use App\Enums\Formats\DatetimeEnum;
use App\Models\Reports\Item;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\StatusFilterTrait;
use Carbon\Carbon;
use EloquentFilter\ModelFilter;

/**
 * @mixin Item
 */
class ItemFilter extends ModelFilter
{
    use IdFilterTrait;
    use StatusFilterTrait;

    public function report(string|int $value): void
    {
        $this->where('report_id', $value);
    }

    public function dateFrom(string $from): void
    {
        $fromDate = Carbon::createFromFormat(DatetimeEnum::DEFAULT_FORMAT, $from);

        $this->where('call_at', '>=', $fromDate);
    }

    public function dateTo(string $to): void
    {
        $toDate = Carbon::createFromFormat(DatetimeEnum::DEFAULT_FORMAT, $to);

        $this->where('call_at', '<=', $toDate);
    }

    public function search(string $value): void
    {
        $this->whereRaw("(name LIKE '%{$value}%' OR num LIKE '%{$value}%')");
    }
}
