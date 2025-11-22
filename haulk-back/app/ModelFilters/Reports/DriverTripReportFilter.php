<?php

namespace App\ModelFilters\Reports;

use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DriverTripReportFilter extends ModelFilter
{
    /**
     * @param string $driver_id
     * @return DriverTripReportFilter
     */
    public function driver(string $driver_id)
    {
        return $this->where('driver_id', (int) $driver_id);
    }

    /**
     * @param string $reportDate
     * @return DriverTripReportFilter
     */
    public function reportDate(string $report_date)
    {
        return $this
            ->where('report_date', '>=', Carbon::make($report_date)->startOfDay())
            ->where('report_date', '<=', Carbon::make($report_date)->endOfDay());
    }

    /**
     * @param string $reportDate
     * @return DriverTripReportFilter
     */
    public function datesRange(string $datesRange)
    {
        $range = explode(' - ', $datesRange);
        $startDate = Carbon::parse($range[0])->startOfDay();
        $endDate = isset($range[1]) ? Carbon::parse($range[1])->endOfDay() : Carbon::parse($range[0])->endOfDay();
        return $this->where(function (Builder $builder) use ($startDate, $endDate){
            return $builder->where(function (Builder $query) use ($startDate, $endDate) {
                return $query->where('date_from', '>=', $startDate)
                    ->where('date_from', '<=', $endDate);
            }
            )->orWhere(
                function (Builder $query) use ($startDate, $endDate) {
                    return $query->where('date_to', '>=', $startDate)
                        ->where('date_to', '<=', $endDate);
                }
            )->orWhere(
                function (Builder $query) use ($startDate, $endDate) {
                    return $query->where('date_from', '>=', $startDate)
                        ->where('date_to', '<=', $endDate);
                });
        });
    }

    /**
     * @param string $reportDate
     * @return DriverTripReportFilter
     */
    public function dateFrom(string $date_from)
    {
        return $this->where('date_from', '>=', Carbon::make($date_from)->startOfDay());
    }

    /**
     * @param string $date_to
     * @return DriverTripReportFilter
     */
    public function dateTo(string $date_to)
    {
        return $this->where('date_to', '<=', Carbon::make($date_to)->endOfDay());
    }
}
