<?php

namespace App\Filters\Commercial;

use App\Enums\Formats\DatetimeEnum;
use App\Filters\BaseModelFilter;
use App\Models\Commercial\CredentialsRequest;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use App\Traits\Filter\StatusFilterTrait;
use Carbon\Carbon;

/**
 * @mixin CredentialsRequest
 */
class CredentialsRequestFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use StatusFilterTrait;
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
        return CredentialsRequest::ALLOWED_SORTING_FIELDS;
    }
}