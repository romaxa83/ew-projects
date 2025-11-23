<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Order;

//use App\Models\Order\Activity;
//use App\Models\Order\Notes;
//use App\Models\Task;
use App\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\{JsonResponse, Request};
use Yajra\DataTables\{EloquentDataTable, Facades\DataTables};
use Str;

class EfficiencyReportController extends Controller
{

    protected static $LOST_STATUS_ID = 9;
    protected static $CALCULATION_DONE_STATUS_ID = 4;
//    protected static $BOOKED_STATUS_ID = 4;
    protected static $SUCCESS_STATUS_ID = 10;
    protected static $LOST_REASON_ID = 1;

    public function view(Request $request)
    {
        $managers = [];
        $users = User::with(['employee' => function ($q) {
//            $q->orderBy('active', 'DESC');
//            $q->orderBy('name', 'ASC');
        }])->whereHas('roles', function ($q) {
            $q->orderManager();
        })->whereNotIn('id', [1, 2, 3])
            ->whereJsonContains('division_ids', session()->get('division.id'))
            ->orderBy('name', 'ASC')
            ->get()
            ->sortByDesc(function ($user, $key) {
                return $user->employee ? (int)$user->employee->active : 0;
            });
        $users->each(function ($item, $key) use (&$managers) {
            $employee = $item->employee ? ' (' . $item->employee->name . ' ' . $item->employee->l_name . ')' : '';
            $fired = $item->employee && !$item->employee->active ? ' (fired)' : '';
            if (!$item->employee)
                $fired = ' (without employee)';
            $managers[] = [
                'id' => $item->id,
                'name' => $item->name . $employee . $fired,
            ];
        });

        return view('layouts.data-tables.efficiency', [
            'managers' => $managers,
            'sources' => Order\Source::whereJsonContains('division_ids', session()->get('division.id'))->get(['id', 'title'])
        ]);
    }


    /**
     * test @see \Tests\Feature\Reports\Efficiency\DatatableTest
     */
    public function datatable(Request $request)
    {

        $Builder = Order::where('division_id', session()->get('division.id'));

        $validated = $request->all();
//        dd($validated);
        $divisionMiscs = session()->get('division.miscs');
        $startPeriodUTC = Carbon::createFromFormat('Y-m-d', $validated['filter']['start-range'], $divisionMiscs['tz'])
            ->startOfDay()->setTimezone(new \DateTimeZone('UTC'));
        $endPeriodUTC = Carbon::createFromFormat('Y-m-d', $validated['filter']['end-range'], $divisionMiscs['tz'])
            ->endOfDay()->setTimezone(new \DateTimeZone('UTC'));

        $Builder->when($validated['filter']['period-type'] == 'by_creation', function ($query) use ($startPeriodUTC, $endPeriodUTC) {
            return $query->whereBetween('created_at', [$startPeriodUTC, $endPeriodUTC]);
        });
        $collection = collect([
            ['id' => 1, 'type' => 'LeadsTotal', 'title' => 'All incoming leads, qty'],
            ['id' => 2, 'type' => 'LeadsLost', 'title' => 'Lost leads, qty'],
            ['id' => 3, 'type' => 'LeadsLostCR', 'title' => 'Lost CR, %'],
//            ['id' => 3, 'type' => 'LeadsCommercial', 'title' => 'Commercial leads'],
            ['id' => 4, 'type' => 'LeadsCalculated', 'title' => 'Calculation Done passed leads, qty'],
            ['id' => 5, 'type' => 'LeadsCalculatedCR', 'title' => 'Calculation Done passed leads CR, %'],
            ['id' => 6, 'type' => 'LeadsCalculatedSum', 'title' => 'Calculation Done, est. sum $'],
            ['id' => 7, 'type' => 'LeadsBooked', 'title' => 'Booked passed leads, qty'],
            ['id' => 8, 'type' => 'LeadsBookedCR', 'title' => 'Booked passed leads CR, %'],
            ['id' => 9, 'type' => 'LeadsBookedSum', 'title' => 'Booked leads, est. sum $'],
            ['id' => 10, 'type' => 'LeadsSuccessful', 'title' => 'Successful leads, qty'],
            ['id' => 11, 'type' => 'LeadsSuccessfulCR', 'title' => 'Successful CR, %'],
//            ['id' => 5, 'type' => 'LeadsMovingScheduled', 'title' => 'Moving scheduled leads'],
//            ['id' => 6, 'type' => 'LeadsMoved', 'title' => 'Moved leads'],
            ['id' => 12, 'type' => 'SuccessRevenue', 'title' => 'Successful revenue, $'],
            ['id' => 13, 'type' => 'SuccessAOV', 'title' => 'Successful AOV, $'],
        ])->keyBy('type');


        if (!empty($validated['filter']['groupBy'])) {
            if (in_array($validated['filter']['groupBy'], ['manager'])) {
                $UsersBuilder = User::with('employee')
                    ->whereHas('roles', function (Builder $q) {
                        $q->orderManager();
                    })
                    ->whereJsonContains('division_ids', session()->get('division.id'))
                    ->when($request->all(), function (Builder $query, $requested) {
                        if (!empty($requested['filter']['managers'])) {
                            $query->whereIn('id', $requested['filter']['managers']);
                        }
                    });
                $Users = $UsersBuilder->get();

//                dd($Users);
                foreach ($Users as $User) {
                    $colname = 'user_' . $User->id;
                    $collection = $collection->mergeRecursive($this->getPeriodReport(
                        $Builder->clone()->where('user_id', $User->id),
                        $startPeriodUTC, $endPeriodUTC, $colname, $User->id));
                }
                $colname = 'user_0';
                $collection = $collection->mergeRecursive($this->getPeriodReport(
                    $Builder->clone()->where(function (Builder $query) {
                        $query->where('user_id', 0)->orWhereNull('user_id');
                    }),
                    $startPeriodUTC, $endPeriodUTC, $colname));


            } elseif (in_array($validated['filter']['groupBy'], ['source'])) {
                $Sources = Order\Source::when($request->all(), function (Builder $query, $requested) {
                    if (!empty($requested['filter']['sources'])) {
                        $query->whereIn('id', $requested['filter']['sources']);
                    }
                })->get();
//                dd($Sources->toArray());
                foreach ($Sources as $Source) {
                    $colname = 'source_' . $Source->id;
                    $collection = $collection->mergeRecursive($this->getPeriodReport(
                        $Builder->clone()->where('source_id', $Source->id),
                        $startPeriodUTC, $endPeriodUTC, $colname));
                }
                $colname = 'source_0';
                $collection = $collection->mergeRecursive($this->getPeriodReport(
                    $Builder->clone()->where(function (Builder $query) {
                        $query->where('source_id', 0)->orWhereNull('source_id');
                    }),
                    $startPeriodUTC, $endPeriodUTC, $colname));


            } elseif (in_array($validated['filter']['groupBy'], ['day', 'month', 'year'])) {
                $startPeriod = Carbon::createFromFormat('Y-m-d', $validated['filter']['start-range'], $divisionMiscs['tz'])->startOfDay();
                $endPeriod = Carbon::createFromFormat('Y-m-d', $validated['filter']['end-range'], $divisionMiscs['tz'])->endOfDay();
                $Period = CarbonPeriod::create($startPeriod, '1 ' . $validated['filter']['groupBy'], $endPeriod);
                foreach ($Period as $key => $startOfInterval) {
                    if ($key == 0)
                        $startDT = $Period->getStartDate()->startOfDay();
                    else
                        $startDT = (clone $startOfInterval)->startOf($validated['filter']['groupBy'])->startOfDay();

                    $endDT = (clone $startOfInterval)->endOf($validated['filter']['groupBy'])->endOfDay();
                    if ($endDT > $Period->getEndDate())
                        $endDT = $Period->getEndDate()->endOfDay();

//                $endInterval = (clone $startPeriod)->endOf($validated['filter']['groupBy']);
//                if ($endInterval > $endPeriod)
//                    $endInterval = clone $endPeriod;

                    $formatted = '';
                    if ($validated['filter']['groupBy'] == 'day') {
                        $formatted = $startOfInterval->format('Y-m-d');
                    } elseif ($validated['filter']['groupBy'] == 'month') {
                        $formatted = $startOfInterval->format('Y-m');
                    } elseif ($validated['filter']['groupBy'] == 'year') {
                        $formatted = $startOfInterval->format('Y');
                    }

                    $Builder->when($validated['filter']['period-type'] == 'by_creation', function ($query) use ($startPeriodUTC, $endPeriodUTC) {
                        return $query->whereBetween('created_at', [$startPeriodUTC, $endPeriodUTC]);
                    });


                    $collection = $collection->mergeRecursive($this->getPeriodReport($Builder->clone()
                        ->when($validated['filter']['period-type'] == 'by_creation', function ($query) use ($startDT, $endDT) {
                            return $query->whereBetween('created_at', [$startDT->setTimezone('UTC'), $endDT->setTimezone('UTC')]);
                        }),
                        $startDT->setTimezone('UTC'), $endDT->setTimezone('UTC'), $formatted));
                }
            }
        } else {
            $totalData = $this->getPeriodReport($Builder, $startPeriodUTC, $endPeriodUTC, 'total');
            $collection = $collection->mergeRecursive($totalData);
        }
        //dd(1);

//        dd($collection);

        return DataTables::collection($collection)->with(['cols' => ['col1', 'col2']])->make();
    }

    private function getPeriodReport(Builder $Builder, Carbon $startPeriodUTC, Carbon $endPeriodUTC, $colname, $manager = null)
    {
        $calculationStatuses = [4, 7, 5, 6, 8, 14, 16, 17, 15, 10];
//        $movingStatuses = [10, 15, 5, 7, 8, 14, 16];
//        $movedStatuses = [10, 15];
        $bookedStatuses = [5];
        $successStatuses = [14, 10];
        $validated = request()->all();

        $LeadsTotal = $Builder->clone()
            ->where(function ($q) {
                $q->where('status_id', 1)->orWhereHas('statusHistory', function ($q) {
                    $q->where('prev_status', 1);
                });
            })
            ->whereBetween('created_at', [$startPeriodUTC, $endPeriodUTC])->count();

        $LeadsLost = $Builder->clone()->with('extended')
            ->where('status_id', self::$LOST_STATUS_ID)
            ->when($validated['filter']['period-type'] == 'by_status_changed', function ($query) use ($startPeriodUTC, $endPeriodUTC) {
                return $query->whereHas('statusHistoryLatest', function (Builder $q) use ($startPeriodUTC, $endPeriodUTC) {
                    $q->where('new_status', self::$LOST_STATUS_ID)
                        ->whereBetween('created_at', [$startPeriodUTC, $endPeriodUTC]);
                });
            })

//            ->whereHas('extended', function (Builder $q) {
//                $q->whereJsonContains('miscs->order->closing_reason_id', self::$LOST_REASON_ID);
//            })
            ->count();
        $LeadsCommercial = $LeadsTotal - $LeadsLost; // может быть отрицательно кол-во

        $LeadsCalculated = $Builder->clone()->with(['statusHistory', 'calculated'])
            ->when($validated['filter']['period-type'] == 'by_creation', function ($query) {
                return $query->whereHas('statusHistory', function (Builder $q) {
                    $q->whereIn('new_status', [self::$CALCULATION_DONE_STATUS_ID]);
                });
            })
            ->when($validated['filter']['period-type'] == 'by_status_changed', function ($query) use ($startPeriodUTC, $endPeriodUTC) {
                return $query->whereHas('statusHistory', function (Builder $q) use ($startPeriodUTC, $endPeriodUTC) {
                    $q->where('new_status', self::$CALCULATION_DONE_STATUS_ID)
                        ->whereBetween('created_at', [$startPeriodUTC, $endPeriodUTC]);
                });
            })
            ->get();

        $LeadsCalculatedEstimatedSum = 0;
        if ($LeadsCalculated->isNotEmpty()) {
            $i = 0;
            foreach ($LeadsCalculated as $Lead) {
                $total = $Lead->calculated->where('title', 'total');
                if ($total->isNotEmpty()) {
                    $total = $total->first();
                    $value = preg_replace('/(.*?-\s)?\$/m', '', $total->value);
                    $value = preg_replace('/\,/m', '', $value);
                    $LeadsCalculatedEstimatedSum += +$value;
                }
                $i++;
            }
        }

        $LeadsBookedCollection = $Builder->clone()->with(['statusHistory', 'calculated'])
            ->when($validated['filter']['period-type'] == 'by_creation', function ($query) use ($bookedStatuses) {
                return $query->whereHas('statusHistory', function (Builder $q) use ($bookedStatuses) {
                    $q->whereIn('new_status', $bookedStatuses);
                });
            })
            ->when($validated['filter']['period-type'] == 'by_status_changed', function ($query) use ($startPeriodUTC, $endPeriodUTC, $bookedStatuses) {
                return $query->whereHas('statusHistory', function (Builder $q) use ($startPeriodUTC, $endPeriodUTC, $bookedStatuses) {
                    $q->whereIn('new_status', $bookedStatuses)
                        ->whereBetween('created_at', [$startPeriodUTC, $endPeriodUTC]);
                });
            })
            ->get();

        $LeadsBookedSum = 0;
        $LeadsBooked = $LeadsBookedCollection->count();
        if ($LeadsBookedCollection->isNotEmpty()) {
            foreach ($LeadsBookedCollection as $Lead) {
                /** @var $Lead Order */
                $total = $Lead->calculated->where('title', 'total');
                if ($total->isNotEmpty()) {
                    $total = $total->first();
                    $value = preg_replace('/(.*?-\s)?\$/m', '', $total->value);
                    $value = preg_replace('/\,/m', '', $value);
                    $LeadsBookedSum += +$value;
                }
            }
        }
//        $LeadsCalculated = $Builder->clone()->with('statusHistoryLatest')
//            ->whereIn('status_id', $calculationStatuses)
//            ->whereHas('statusHistoryLatest', function (Builder $q) use ($startPeriodUTC, $endPeriodUTC) {
//                $q->whereColumn('new_status', 'orders.status_id')
//                    ->whereBetween('created_at', [$startPeriodUTC, $endPeriodUTC]);
//            })->count();

//        $LeadsMovingScheduled = $Builder->clone()
//            ->with('statusHistoryLatest')
//            ->whereIn('status_id', $movingStatuses)
//            ->whereHas('statusHistoryLatest', function (Builder $q) use ($startPeriodUTC, $endPeriodUTC) {
//                $q->whereColumn('new_status', 'orders.status_id')
//                    ->whereBetween('created_at', [$startPeriodUTC, $endPeriodUTC]);
//            })->count();
//
//        $LeadsMoved = $Builder->clone()->with('statusHistoryLatest')
//            ->whereIn('status_id', $movedStatuses)
//            ->whereHas('statusHistoryLatest', function (Builder $q) use ($startPeriodUTC, $endPeriodUTC) {
//                $q->whereColumn('new_status', 'orders.status_id')
//                    ->whereBetween('created_at', [$startPeriodUTC, $endPeriodUTC]);
//            })->count();


//        $LeadsSuccess = $Builder->clone()->with('payments')
//            ->where('status_id', self::$SUCCESS_STATUS_ID)
//            ->whereHas('statusHistoryLatest', function (Builder $q) use ($startPeriodUTC, $endPeriodUTC) {
//                $q->whereColumn('new_status', 'orders.status_id')
//                    ->whereBetween('created_at', [$startPeriodUTC, $endPeriodUTC]);
//            })->get();
        $LeadsSuccess = $Builder->clone()->with('payments')
            ->with('statusHistoryLatest')
            ->whereIn('status_id', $successStatuses)
            ->when($validated['filter']['period-type'] == 'by_status_changed', function ($query) use ($startPeriodUTC, $endPeriodUTC, $successStatuses) {
                return $query->whereHas('statusHistoryLatest', function (Builder $q) use ($startPeriodUTC, $endPeriodUTC, $successStatuses) {
                    $q->whereIn('new_status', $successStatuses)
                        ->whereBetween('created_at', [$startPeriodUTC, $endPeriodUTC]);
                });
            })
//            ->whereIn('status_id', $movingStatuses)
            ->get();


        $SuccessRevenue = 0;
        $SuccessAOV = 0;
        if ($LeadsSuccess->isNotEmpty()) {
            foreach ($LeadsSuccess as $Lead) {
                if ($Lead->payments) {
                    $SuccessRevenue += $Lead->payments->reduce(function (?float $carry, $item) {
                        return $carry + $item->amount;
                    });
                }
            }
            $SuccessAOV = round($SuccessRevenue / $LeadsSuccess->count(), 2);
            $SuccessRevenue = round($SuccessRevenue, 2);
        }


        return [
            'LeadsTotal' => [$colname => empty($LeadsTotal) ? '' : $LeadsTotal],
            'LeadsLost' => [$colname => empty($LeadsLost) ? '' : $LeadsLost],
            'LeadsLostCR' => [$colname => !empty($LeadsTotal) ? round(100 * $LeadsLost / $LeadsTotal, 2) . '%' : ''],
//            'LeadsLowQuality' => [$colname => empty($LeadsLowQuality) ? '' : $LeadsLowQuality],
//            'LeadsCommercial' => [$colname => empty($LeadsCommercial) ? '' : $LeadsCommercial],
            'LeadsCalculated' => [$colname => $LeadsCalculated->isEmpty() ? '' : $LeadsCalculated->count()],
            'LeadsCalculatedCR' => [$colname => !empty($LeadsTotal) ? round(100 * $LeadsCalculated->count() / $LeadsTotal, 2) . '%' : ''],
            'LeadsCalculatedSum' => [$colname => !empty($LeadsCalculatedEstimatedSum) ? '$' . $LeadsCalculatedEstimatedSum : ''],
            'LeadsBooked' => [$colname => empty($LeadsBooked) ? '' : $LeadsBooked],
            'LeadsBookedCR' => [$colname => !empty($LeadsTotal) ? round(100 * $LeadsBooked / $LeadsTotal, 2) . '%' : ''],
            'LeadsBookedSum' => [$colname => !empty($LeadsBookedSum) ? '$' . $LeadsBookedSum : ''],
            'LeadsSuccessful' => [$colname => $LeadsSuccess->isEmpty() ? '' : $LeadsSuccess->count()],
            'LeadsSuccessfulCR' => [$colname => !empty($LeadsTotal) ? round(100 * $LeadsSuccess->count() / $LeadsTotal, 2) . '%' : ''],
//            'LeadsMovingScheduled' => [$colname => empty($LeadsMovingScheduled) ? '' : $LeadsMovingScheduled],
//            'LeadsMoved' => [$colname => empty($LeadsMoved) ? '' : $LeadsMoved],
            'SuccessRevenue' => [$colname => empty($SuccessRevenue) ? '' : '$' . $SuccessRevenue],
            'SuccessAOV' => [$colname => empty($SuccessAOV) ? '' : '$' . $SuccessAOV],
        ];

//        return collect([
//            ['type' => 'LeadsTotal', $colname => $LeadsTotal],
//            ['type' => 'LeadsLowQuality', $colname => $LeadsLowQuality],
//            ['type' => 'LeadsCommercial', $colname => $LeadsCommercial],
//            ['type' => 'LeadsCalculated', $colname => $LeadsCalculated],
//            ['type' => 'LeadsMovingScheduled', $colname => $LeadsMovingScheduled],
//            ['type' => 'LeadsMoved', $colname => $LeadsMoved],
//            ['type' => 'SuccessRevenue', $colname => $SuccessRevenue],
//            ['type' => 'SuccessAOV', $colname => $SuccessAOV],
//        ]);
    }

}
