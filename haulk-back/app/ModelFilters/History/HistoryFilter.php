<?php

namespace App\ModelFilters\History;

use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class HistoryFilter extends ModelFilter
{
    /**
     * @param string $category
     * @return HistoryFilter
     */
    public function category(string $category)
    {
        return $this->where(function (Builder $query) use ($category) {
            return $query->where('model_type', $category);
        });
    }

    /**
     * @param string $category
     * @return HistoryFilter
     */
    public function user(int $id)
    {
        return $this->where(function (Builder $query) use ($id) {
            return $query->where('user_id', $id);
        });
    }

    /**
     * @param string $date_from
     * @return HistoryFilter
     */
    public function dateFrom(string $date_from)
    {
        return $this->where(function (Builder $query) use ($date_from) {
            return $query->where('performed_at', '>=', $date_from . '00:00:00');
        });
    }

    /**
     * @param string $date_to
     * @return HistoryFilter
     */
    public function dateTo(string $date_to)
    {
        return $this->where(function (Builder $query) use ($date_to) {
            return $query->where('performed_at', '<=', $date_to . '23:59:59');
        });
    }

    /**
     * @param string $reportDate
     * @return HistoryFilter
     */
    public function datesRange(string $datesRange)
    {
        $range = explode(' - ', $datesRange);
        $startDate = Carbon::parse($range[0])->startOfDay();
        $endDate = isset($range[1]) ? Carbon::parse($range[1])->endOfDay() : Carbon::parse($range[0])->endOfDay();

        return $this->where(function (Builder $query) use ($startDate, $endDate) {
            return $query->where('performed_at', '>=', $startDate)
                ->where('performed_at', '<=', $endDate);
        });
    }
}
