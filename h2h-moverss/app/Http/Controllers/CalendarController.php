<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Calendars\CalendarService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\{JsonResponse, Request};
use DateInterval, DatePeriod;

/**
 * Calendar of works, display for a month and for 7 days.
 */
class CalendarController extends Controller
{
    protected CalendarService $service;

    public function __construct()
    {
        $this->service = resolve(CalendarService::class);
    }

    /**
     * Displaying calendar schedule.
     * @param  Request  $request
     * @return Renderable
     */
    public function schedule(Request $request): Renderable
    {
        $current_day = now();
        $days_names = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        $calendar_date = $request->filled('calendar_date') ? Carbon::parse($request->calendar_date) : now();
        $week_date = $request->filled('week_date') ? Carbon::parse($request->week_date) : now();

        $date_start = $calendar_date->clone()->startOfMonth()->startOfWeek(CarbonInterface::SUNDAY);
        $date_end = $calendar_date->clone()->endOfMonth()->endOfWeek(CarbonInterface::SATURDAY);
//        $date_end = $calendar_date->clone()->modify('last day of this month')->addDay();

        return view('layouts.calendar.body', [
            'days_names' => $days_names,
            'date' => $calendar_date,
            'week_date' => $week_date,
            'works_on_week' => $this->worksOnWeek($week_date),
            'current_day' => $current_day,
            'calendar' => $this->generateCalendar($date_start, $date_end, $current_day),
        ]);
    }

    /**
     * Drawing AJAX cell info about orders in selected statuses.
     * @param  Request  $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function cellInfo(Request $request): JsonResponse
    {
        $in_calendar_statuses = $this->inCalendarStatuses();

        $records = Order\Work::query()
            ->withTotalPayments()
            ->with([
                'workTypes:title,orders_works_2_work.*',
                'order',
                'order.status',
                'order.client',
                'order.estimate:order_id,type,calculated_moving_min_value,calculated_moving_max_value',
            ])
            ->where('start_date', $request->date)
            ->whereHas('order', function ($q) use ($request, $in_calendar_statuses) {
                $q->whereIn('status_id', $in_calendar_statuses)
                    ->whereJsonContains('division_id', $request->session()->get('division.id'));
            })
            ->get()
            ->transform(function ($work) {

                $order = $work->order;
                $work->loadMissing([
                    'order.estimate.'.$work->order->estimate->type,
                    'order.calculated' => function ($q) use (&$order) {
                        $q->where('estimate_type', $order->estimate->type);
                    },
                ]);
                $work->order->payments_sum = $work->order->payments->sum('amount');

                // Меняем ключ
                $work->order->calculated = $work->order->calculated->keyBy('title')->toArray();

                $start_at = 'n/a';
                if ($work->start_date && $work->start_time && $work->start_time_to) {
                    $t_1 = Carbon::parse($work->start_time)->format('g:i A');
                    $t_2 = Carbon::parse($work->start_time_to)->format('g:i A');

                    $start_at = '<b>Start Time Range:</b><br />'.$t_1.' - '.$t_2;
                } elseif ($work->start_date && $work->start_time) {
                    $start_at = '<b>Start Time:</b><br />'.Carbon::parse($work->start_date.' '.$work->start_time)
                            ->format('g:i A');
                }
                $work->start_at = $start_at;

                return $work;
            });

        return response()
            ->json([
                'success' => true,
                'html' => view('layouts.calendar.tabs.schedule.cellInfo', [
                    'records' => $records,
                ])
                    ->render(),
            ]);
    }

    /**
     * Generation calendar days + filling with statistics.
     * @param  Carbon  $date_start
     * @param  Carbon  $date_end
     * @param  Carbon  $currentDay
     * @return array
     */
    protected function generateCalendar(Carbon $date_start, Carbon $date_end, Carbon $currentDay): array
    {
        $calendar = [];

        $divisionId = request()->session()->get('division.id');
        $divisionTz = request()->session()->get('division.miscs.tz');

        $stat = $this->service->statistics(
            $date_start,
            $date_end,
            $divisionId,
            $divisionTz,
        );

        $range = new DatePeriod($date_start, new DateInterval('P1D'), $date_end);

        foreach ($range as $dt) {
            $day = [
                'dt' => $dt,
                'is_today' => $dt->format('Y-m-d') === $currentDay->format('Y-m-d'),
                'loading-class' => 'bg-secondary',
            ];
            $date = $dt->format('Y-m-d');


            if (isset($stat[$date])) {
                $stat_r = $stat[$date];

                $day['stat'] = $stat_r;
            }

            $calendar[$date] = $day;
        }

        return $calendar;
    }

    /**
     * Get statistics for period.
     * @param  Carbon  $date_start
     * @param  Carbon  $date_end
     * @return array
     */
    private function statistics(Carbon $date_start, Carbon $date_end): array
    {
        $in_calendar_statuses = $this->inCalendarStatuses();

        $stat = [];
        Order\Work::whereBetween('start_date',
            [$date_start->format('Y-m-d'), $date_end->format('Y-m-d')])
            ->withCount(['dispatchTrucks', 'dispatchEmployees'])
            ->with('order')
            ->whereHas('order', function ($q) {
                $q->whereJsonContains('division_id', request()->session()->get('division.id'));
            })
            ->get()
            ->each(function ($item) use ($in_calendar_statuses, &$stat) {
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

        return $stat;
    }

    /**
     * Get list of works for the week.
     * @param  Carbon  $week_date  Date for determining the week.
     * @return array
     */
    private function worksOnWeek(Carbon $week_date): array
    {
        $in_calendar_statuses = $this->inCalendarStatuses();

//        $week_first_day = $week_date->clone()->modify('monday this week');
//        $week_last_day = $week_date->clone()->modify('sunday this week');
        $week_first_day = $week_date->clone()->modify('sunday previous week');
        $week_last_day = $week_date->clone()->modify('saturday this week');

        $records = Order\Work::query()
            ->withTotalPayments()
            ->with([
                'workTypes:title,orders_works_2_work.*',
                'order',
                'order.status',
                'order.client:id,name',
                'order.estimate:order_id,type,calculated_moving_min_value,calculated_moving_max_value',
            ])
            ->whereBetween('start_date',
                [$week_first_day->format('Y-m-d').' 00:00:00', $week_last_day->format('Y-m-d').' 23:59:59'])
            ->whereHas('order', function ($q) use ($in_calendar_statuses) {
                $q->whereIn('status_id', $in_calendar_statuses);
            })
            ->get()
            ->transform(function ($work) {
                $timeWindow = null;
                if ($work->start_time && $work->start_time_to) {
                    $t_1 = Carbon::parse($work->start_time)->format('g:i A');
                    $t_2 = Carbon::parse($work->start_time_to)->format('g:i A');

                    $timeWindow = $t_1.' - '.$t_2.' Time Arrival Window';
                } elseif ($work->start_time) {
                    $timeWindow = Carbon::parse($work->start_date.' '.$work->start_time)->format('g:i A').
                        ' Time Arrival';
                }

                $work->timeWindow = $timeWindow;

                $work->loadMissing([
                    'order.estimate.'.$work->order->estimate->type,
                ]);
                $work->order->payments_sum = $work->order->payments->sum('amount');

                return $work;
            })
            ->groupBy('start_date')
            ->map(function ($group) {
                return $group->sortBy('start_time');
            });

        $range = [];
        $_range = new DatePeriod($week_first_day, new DateInterval('P1D'), $week_last_day->addDay());

        foreach ($_range as $dt) {
            $range[$dt->format('Y-m-d')] = [
                'dt' => $dt,
            ];
        }

        return [
            'dates' => [
                'weekFirst' => $week_first_day,
                'weekLast' => $week_last_day,
                'range' => $range,
            ],
            'records' => $records,
        ];
    }

    /**
     * Statuses for display in the calendar (Popover).
     * @return array
     */
    private function inCalendarStatuses(): array
    {
        return Order\Status::whereJsonContains('actions', 'enable_dispatch')->get('id')
            ->pluck('id')->all();
    }

}
