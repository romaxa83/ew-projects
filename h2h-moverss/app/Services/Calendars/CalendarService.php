<?php

namespace App\Services\Calendars;

use App\Models\Order;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

final class CalendarService
{
    public function inCalendarStatuses(): array
    {
        return Order\Status::whereJsonContains('actions', 'enable_dispatch')
            ->get('id')
            ->pluck('id')
            ->all();
    }

    public function statistics(
        Carbon|CarbonImmutable $date_start,
        Carbon|CarbonImmutable $date_end,
        $divisionId,
        $divisionTz,
    ): array
    {
        $in_calendar_statuses = $this->inCalendarStatuses();

        $stat = [];
        Order\Work::whereBetween('start_date',
            [$date_start->format('Y-m-d'), $date_end->format('Y-m-d')])
            ->withCount(['dispatchTrucks', 'dispatchEmployees'])
            ->with('order')
            ->whereHas('order', function ($q) use($divisionId) {
                $q->whereJsonContains('division_id', $divisionId);
            })
            ->get()
            ->each(function ($item) use ($in_calendar_statuses, &$stat) {
                /** @var $item Order\Work */
                $date = $item['start_date'];

                if (!isset($stat[$date])) {
                    $stat[$date] = [
                        'trucks_total' => 0,
                        'trucks_assigned' => 0,
                        'employees_total' => 0,
                        'employees_assigned' => 0,
                        'booked_works' => 0,
                        'display_info' => false,
                    ];
                }

                if ($item['in_dispatch']) {
                    $stat[$date]['booked_works']++;
                }

                if (in_array($item->order->status_id, $in_calendar_statuses, true)) {
                    $stat[$date]['display_info'] = true;
                }

                $stat[$date]['trucks_total'] += (int) $item['trucks'];
                $stat[$date]['trucks_assigned'] += (int) $item['dispatch_trucks_count'];
                $stat[$date]['employees_total'] += (int) $item['employees'];
                $stat[$date]['employees_assigned'] += (int) $item['dispatch_employees_count'];
            });

        foreach ($stat as $date => $item) {
//            $startPeriod = CarbonImmutable::createFromFormat('Y-m-d', $date, $divisionTz)
//                ->startOfDay()
//                ->setTimezone(new \DateTimeZone('UTC'))
//            ;
//            $endPeriod = CarbonImmutable::createFromFormat('Y-m-d', $date, $divisionTz)
//                ->endOfDay()
//                ->setTimezone(new \DateTimeZone('UTC'))
//            ;

//            $count = Order::whereBetween('created_at', [$startPeriod, $endPeriod])
            $count = Order::query()
                ->where('division_id', $divisionId)
                ->whereNotIn('status_id', [Order\Status::SALES_DONE_ID])
                ->whereHas('works', function ($q) use ($date) {
                        $q->where('start_date', $date)
                            ->where(function ($subQuery) {
                                $subQuery->has('dispatchTrucks')
                                    ->orHas('dispatchEmployees');
                            })
                        ;
                })
                ->count();

            $stat[$date]['not_close_order'] = $count;
        }

        return $stat;
    }
}

