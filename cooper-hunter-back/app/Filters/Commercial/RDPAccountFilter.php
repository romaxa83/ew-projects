<?php

namespace App\Filters\Commercial;

use App\Enums\Formats\DatetimeEnum;
use App\Filters\BaseModelFilter;
use App\Models\Commercial\RDPAccount;
use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use Carbon\Carbon;

/**
 * @mixin RDPAccount
 */
class RDPAccountFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use ActiveFilterTrait;
    use SortFilterTrait;

    public function endDateFrom(string $from): void
    {
        $fromDate = Carbon::createFromFormat(DatetimeEnum::DATE, $from);

        $this->where('end_date', '>=', $fromDate->startOfDay());
    }

    public function endDateTo(string $to): void
    {
        $toDate = Carbon::createFromFormat(DatetimeEnum::DATE, $to);

        $this->where('end_date', '<=', $toDate->endOfDay());
    }

    protected function allowedOrders(): array
    {
        return RDPAccount::ALLOWED_SORTING_FIELDS;
    }
}