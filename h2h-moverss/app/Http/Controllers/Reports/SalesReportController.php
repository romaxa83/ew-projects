<?php

namespace App\Http\Controllers\Reports;

use App\Enums\Employee\SalesTeamEnum;
use App\Enums\Orders\EstimateTypeEnum;
use App\Enums\Orders\MoveTypeEnum;
use App\Enums\Reports\ReportColumnEnum;
use App\Exports\Reports\SalesReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\SalesFilterRequest;
use App\Models\Employee\EfficiencyPlan;
use App\Models\Employee\SalesPlan;
use App\Models\Order;
use App\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Http\{JsonResponse, Request};
use Yajra\DataTables\{ Facades\DataTables};
use Maatwebsite\Excel\Facades\Excel;

class SalesReportController extends Controller
{
    private ?string $targetDate_YM = null;
    private array $months = [];

    const YEAR_PATTERN = '/^\d{4}$/';

    public function view(Request $request)
    {
        logger_info('SALES REPORT VIEW');

        $managers = [];

        $users = User::query()
            ->with(['employee'])
            ->whereHas('employee', function ($q) {
                $q->whereNotNull('sales_team');
            })
            ->whereHas('roles', function ($q) {
             $q->orderManager();
            })

//            ->whereNotIn('id', [1, 2, 3])
            ->whereJsonContains('division_ids', session()->get('division.id'))
            ->orderBy('name', 'ASC')
            ->get()
            ->sortByDesc(function (User $user) {
                return $user->employee ? (int)$user->employee->active : 0;
            });

        $users->each(function ($item) use (&$managers) {
            /** @var $item User */
            $employee = $item->employee ? $item->employee->full_name : $item->name;
            $fired = $item->employee && !$item->employee->active ? ' (fired)' : '';
            if (!$item->employee)
                $fired = ' (without employee)';

            $managers[] = [
                'id' => $item->id,
                'name' => $employee . $fired,
                'sales_team' => $item?->employee?->sales_team?->value,
            ];
        });

        return view('layouts.data-tables.sales-report', [
            'managers' => $managers,
            'sales_teams' => SalesTeamEnum::forSelect('key'),
            'move_types' => MoveTypeEnum::forSelect('key'),
        ]);
    }

    public function exportCsv(SalesFilterRequest $request): JsonResponse
    {
        $filter = $request->validated()['filter'];

        $division = session()->get('division');

        $this->targetDate_YM = $filter['date'];

        $filter['division_id'] = $division['id'] ?? (int)$filter['division_id'];
        $filter['division_tz'] = $division['miscs']['tz'] ?? $filter['division_tz'];
        if(isset($filter['period-type'])){
            $filter['period-type'] = 'by_creation';
        }

        $filename = 'sales_report.csv';
        $storagePath = 'app/public/reports/';
        $filepath = storage_path($storagePath);

        // если папки нет - создаем
        if (!file_exists($filepath)) {
            mkdir($filepath, 0777, true);
        }
        $file = fopen($filepath . $filename, 'w');

        try {
            $collection = $this->getCollectionData($filter);
            $data = $collection->all();

            $header = self::getUsersNameForHeader($data);

            fputcsv($file, $header);

            foreach ($data as $item) {
                unset(
                    $item['id'],
                    $item['type'],
                );

                fputcsv($file, $item);
            }

        }catch (\Exception $e){
            fclose($file);
            return $this->responseErrorJson(
                $e->getMessage(),
                $e->getCode(),
            );
        }

        fclose($file);

        return $this->responseDataJson([
            'success' => true,
            'link' => asset('storage/reports/' . $filename)
        ]);
    }

    public function exportExcel(SalesFilterRequest $request): JsonResponse
    {
        $filter = $request->validated()['filter'];

        $division = session()->get('division');

        $this->targetDate_YM = $filter['date'];

        $filter['division_id'] = $division['id'] ?? (int)$filter['division_id'];
        $filter['division_tz'] = $division['miscs']['tz'] ?? $filter['division_tz'];
        if(isset($filter['period-type'])){
            $filter['period-type'] = 'by_creation';
        }

        try {
            $directory = 'public/reports';
            $files = \Storage::files($directory);
            foreach ($files as $file) {
                if (substr($file, -5) == '.xlsx') {
                    \Storage::delete($file);
                }
            }
            $collection = $this->getCollectionData($filter);
            $data = $collection->all();

            $filename = "reports/sales_report.xlsx";

            Excel::store(new SalesReportExport($data), $filename,'public');

        }catch (\Exception $e){
            return $this->responseErrorJson(
                $e->getMessage(),
                $e->getCode(),
            );
        }

        return $this->responseDataJson([
            'success' => true,
            'link' => asset('storage/' . $filename)
        ]);
    }

    /**
     * test @see \Tests\Feature\Reports\Sales\DatatableTest
     */
    public function datatable(SalesFilterRequest $request)
    {
        $filter = $request->validated()['filter'];

        $division = session()->get('division');

        $filter['division_id'] = $division['id'] ?? (int)$filter['division_id'];
        $filter['division_tz'] = $division['miscs']['tz'] ?? $filter['division_tz'];

        if(!array_key_exists('period-type', $filter)){
            $filter['period-type'] = 'by_creation';
        }

        $dateString = $filter['date'];

        // если фильтр по году
        if(preg_match(self::YEAR_PATTERN, $dateString)) {
            $yearStart = CarbonImmutable::createFromFormat('Y', $dateString, $filter['division_tz'])
                ->startOfYear()->setTimezone(new \DateTimeZone('UTC'));
            $months = [];
            // формируем массив месяцев, если это текущий год
            if ($yearStart->isCurrentYear()) {
                $monthEnd = CarbonImmutable::now($filter['division_tz'])
                    ->endOfMonth()->setTimezone(new \DateTimeZone('UTC'));
            } else {
                $monthEnd = CarbonImmutable::createFromFormat('Y', $dateString, $filter['division_tz'])
                    ->endOfYear()->setTimezone(new \DateTimeZone('UTC'));
            }

            $dates = CarbonImmutable::parse($yearStart)->range($monthEnd, '1 month');
            foreach ($dates as $date) {
                $months[] = $date->format('Y-m');
            }

            if(!empty($months)){
                $this->months = $months;
            }
            $filter['months'] = $this->months;

//            dd($months);
//
//            // получаем данные по каждому месяцу
//            $tmp = [];
//            foreach ($months as $month) {
//                $filter['date'] = $month;
//                $this->targetDate_YM = $month;
//
////                dd($filter);
//
//                $tmp[] = $this->getCollectionData($filter)->all();
//            }
//
//            // суммируем данные
//            $collection = $this->aggregateDateForYear($tmp);


            $collection = $this->getCollectionData($filter);
        } else {
            $this->targetDate_YM = $dateString;
            $filter['date'] = $dateString;
            $collection = $this->getCollectionData($filter);
        }

        return DataTables::collection($collection)
            ->setRowClass(function ($row) {
                return $row['color'] === '#2b9ddf' ? 'table-warnin' : 'table-secondary';
            })

            ->with(['cols' => ['col1', 'col2']])
            ->make();
    }

    public function aggregateDateForYear(array $data): Collection
    {
        $leadTotal = [];
        $leadLost = [];
        $leadsLostCR = [];
        $leadsCalculated = [];
        $leadsCalculatedCR = [];
        $leadsCalculatedSum = [];
        $leadsBooked = [];
        $leadsBookedCR = [];
        $leadsBookedSum = [];
        $leadsBookedFromCalculated = [];
        $leadsBookedFromCalculatedCR = [];
        $leadsBookedFromCalculatedSum = [];
        $leadsSuccessful = [];
        $leadsSuccessfulCR = [];
        $successRevenue = [];
        $successAOV = [];
        $leadsDuplicate = [];
        $leadsBadZip = [];
        $leadsCantService = [];
        $salesPlan = [];
        $salesPlanQty = [];
        $left = [];
        $leftQty = [];
        $salesFactCR = [];
        $salesRank = [];
        $conversionPlan = [];
        $conversionFact = [];
        $efficiencyRank = [];
        foreach ($data as $item) {
            $leadTotal[] = $item[ReportColumnEnum::LeadsTotal()];
            $leadLost[] = $item[ReportColumnEnum::LeadsLost()];
            $leadsLostCR[] = $item[ReportColumnEnum::LeadsLostCR()];
            $leadsCalculated[] = $item[ReportColumnEnum::LeadsCalculated()];
            $leadsCalculatedCR[] = $item[ReportColumnEnum::LeadsCalculatedCR()];
            $leadsCalculatedSum[] = $item[ReportColumnEnum::LeadsCalculatedSum()];
            $leadsBooked[] = $item[ReportColumnEnum::LeadsBooked()];
            $leadsBookedCR[] = $item[ReportColumnEnum::LeadsBookedCR()];
            $leadsBookedSum[] = $item[ReportColumnEnum::LeadsBookedSum()];
            $leadsBookedFromCalculated[] = $item[ReportColumnEnum::LeadsBookedFromCalculated()];
            $leadsBookedFromCalculatedCR[] = $item[ReportColumnEnum::LeadsBookedFromCalculatedCR()];
            $leadsBookedFromCalculatedSum[] = $item[ReportColumnEnum::LeadsBookedFromCalculatedSum()];
            $leadsSuccessful[] = $item[ReportColumnEnum::LeadsSuccessful()];
            $leadsSuccessfulCR[] = $item[ReportColumnEnum::LeadsSuccessfulCR()];
            $successRevenue[] = $item[ReportColumnEnum::SuccessRevenue()];
            $successAOV[] = $item[ReportColumnEnum::SuccessAOV()];
            $leadsDuplicate[] = $item[ReportColumnEnum::LeadsDuplicate()];
            $leadsBadZip[] = $item[ReportColumnEnum::LeadsBadZip()];
            $leadsCantService[] = $item[ReportColumnEnum::LeadsCantService()];
            $salesPlan[] = $item[ReportColumnEnum::SalesPlan()];
            $salesPlanQty[] = $item[ReportColumnEnum::SalesPlanQty()];
            $left[] = $item[ReportColumnEnum::Left()];
            $leftQty[] = $item[ReportColumnEnum::LeftQty()];
            $salesFactCR[] = $item[ReportColumnEnum::SalesFactCR()];
            $salesRank[] = $item[ReportColumnEnum::SalesRank()];
            $conversionPlan[] = $item[ReportColumnEnum::ConversionPlan()];
            $conversionFact[] = $item[ReportColumnEnum::ConversionFact()];
            $efficiencyRank[] = $item[ReportColumnEnum::EfficiencyRank()];
        }

//        dd(
//            $successAOV, $salesPlan, $salesPlanQty
//        );

        return collect([
            ReportColumnEnum::LeadsTotal() => $this->aggregateCount($leadTotal),
            ReportColumnEnum::LeadsLost() => $this->aggregateCount($leadLost),
            ReportColumnEnum::LeadsLostCR() => $this->aggregatePercent($leadsLostCR),
            ReportColumnEnum::LeadsCalculated() => $this->aggregateCount($leadsCalculated),
            ReportColumnEnum::LeadsCalculatedCR() => $this->aggregatePercent($leadsCalculatedCR),
            ReportColumnEnum::LeadsCalculatedSum() => $this->aggregateSum($leadsCalculatedSum),
            ReportColumnEnum::LeadsBooked() => $this->aggregateCount($leadsBooked),
            ReportColumnEnum::LeadsBookedCR() => $this->aggregatePercent($leadsBookedCR),
            ReportColumnEnum::LeadsBookedSum() => $this->aggregateSum($leadsBookedSum),
            ReportColumnEnum::LeadsBookedFromCalculated() => $this->aggregateCount($leadsBookedFromCalculated),
            ReportColumnEnum::LeadsBookedFromCalculatedCR() => $this->aggregatePercent($leadsBookedFromCalculatedCR),
            ReportColumnEnum::LeadsBookedFromCalculatedSum() => $this->aggregateSum($leadsBookedFromCalculatedSum),
            ReportColumnEnum::LeadsSuccessful() => $this->aggregateCount($leadsSuccessful),
            ReportColumnEnum::LeadsSuccessfulCR() => $this->aggregatePercent($leadsSuccessfulCR),
            ReportColumnEnum::SuccessRevenue() => $this->aggregateSum($successRevenue),
            ReportColumnEnum::SuccessAOV() => $this->aggregateSum($successAOV),
            ReportColumnEnum::LeadsDuplicate() => $this->aggregateCount($leadsDuplicate),
            ReportColumnEnum::LeadsBadZip() => $this->aggregateCount($leadsBadZip),
            ReportColumnEnum::LeadsCantService() => $this->aggregateCount($leadsCantService),
            ReportColumnEnum::SalesPlan() => $this->aggregateSum($salesPlan),
            ReportColumnEnum::SalesPlanQty() => $this->aggregateCount($salesPlanQty),
            ReportColumnEnum::Left() => $this->aggregateSum($left),
            ReportColumnEnum::LeftQty() => $this->aggregateCount($leftQty),
            ReportColumnEnum::SalesFactCR() => $this->aggregatePercent($salesFactCR),
            ReportColumnEnum::SalesRank() => $this->aggregateCountAverage($salesRank),
            ReportColumnEnum::ConversionPlan() => $this->aggregatePercent($conversionPlan),
            ReportColumnEnum::ConversionFact() => $this->aggregatePercent($conversionFact),
            ReportColumnEnum::EfficiencyRank() => $this->aggregateCountAverage($efficiencyRank),
        ]);
    }

    private function userData(array $data): array
    {
        $result = [];
        foreach ($data as $item) {
            foreach($item as $key => $value){
                if (str_starts_with($key, 'user_')) {
                    $result[$key][] = $value;
                }
            }
        }

        return $result;
    }

    private function getCollectionData(
        array $filter = []
    ): Collection
    {
        if(isset($filter['months']) && !empty($filter['months'])){
            if(count($filter['months']) == 1){
                $fromDateUTC = CarbonImmutable::createFromFormat('Y-m', $filter['months'][0], $filter['division_tz'])
                    ->startOfMonth()->setTimezone(new \DateTimeZone('UTC'));
                $toDateUTC = CarbonImmutable::createFromFormat('Y-m', $filter['months'][0], $filter['division_tz'])
                    ->endOfMonth()->setTimezone(new \DateTimeZone('UTC'));
            } else {
                $fromDateUTC = CarbonImmutable::createFromFormat('Y-m', current($filter['months']), $filter['division_tz'])
                    ->startOfMonth()->setTimezone(new \DateTimeZone('UTC'));
                $toDateUTC = CarbonImmutable::createFromFormat('Y-m', last($filter['months']), $filter['division_tz'])
                    ->endOfMonth()->setTimezone(new \DateTimeZone('UTC'));
            }
        } else {
            $fromDateUTC = CarbonImmutable::createFromFormat('Y-m', $filter['date'], $filter['division_tz'])
                ->startOfMonth()->setTimezone(new \DateTimeZone('UTC'));
            $toDateUTC = CarbonImmutable::createFromFormat('Y-m', $filter['date'], $filter['division_tz'])
                ->endOfMonth()->setTimezone(new \DateTimeZone('UTC'));
        }

//dd($fromDateUTC, $toDateUTC);
        $orderBuilder = Order::query()
            ->with(['estimate', 'tags'])
            ->where('division_id', $filter['division_id'])
            ->when(!empty($filter['move_type']), function ($query) use ($filter) {
                if($filter['move_type'] == MoveTypeEnum::Local()){
                    return $query->whereHas('estimate', function ($q) use ($filter) {
                        $q->whereIn('type', [
                            EstimateTypeEnum::Local(),
                            EstimateTypeEnum::Intrastate(),
                        ]);
                    });
                }
                if($filter['move_type'] == MoveTypeEnum::Interstate()){
                    return $query->whereHas('estimate', function ($q) use ($filter) {
                        $q->where('type', EstimateTypeEnum::Interstate());
                    });
                }
            })
        ;

        $collection = collect(ReportColumnEnum::dataForSalesTable())->keyBy('type');

        $orderBuilder->when(
            $filter['period-type'] == 'by_creation',
            function ($query) use ($fromDateUTC, $toDateUTC, $filter) {
                return $query->whereBetween('created_at', [$fromDateUTC, $toDateUTC]);
            });

        $usersBuilder = User::query()
            ->with([
                'employee',
                'employee.salesPlans',
            ])
            ->whereHas('roles', function (Builder $q) {
                $q->orderManager();
            })
            ->whereHas('employee', function (Builder $q) {
                $q->whereNotNull('sales_team');
            })
            ->whereJsonContains('division_ids', $filter['division_id'])
            ->when(!empty($filter['managers']), function (Builder $query, $filter) {
                $query->whereIn('id', $filter['managers']);
            })
            ->when(!empty($filter['sales_team']), function (Builder $query) use ($filter) {
                $query->whereHas('employee', function ($q) use ($filter) {
                    $q->where('sales_team', $filter['sales_team']);
                });
            })
        ;
        $users = $usersBuilder->get();

        $salesTeams = [];
        foreach ($users as $user) {
            $colname = 'user_' . $user->id;

            if($user->employee && $user->employee->sales_team){
                if($user->employee->sales_team?->isLocal()){
                    $salesTeams['local'][] = $colname;
                }
                if($user->employee->sales_team?->isLong()){
                    $salesTeams['long'][] = $colname;
                }
            }

            $collection = $collection->mergeRecursive(
                $this->getPeriodReport(
                    $orderBuilder->clone()->where('user_id', $user->id),
                    $fromDateUTC,
                    $toDateUTC,
                    $colname,
                    $user
                )
            );
        }

        $colname = 'user_0';
        $collection = $collection->mergeRecursive(
            $this->getPeriodReport(
                $orderBuilder
                    ->clone()
                    ->where(function (Builder $query) {
                        $query->where('user_id', 0)
                            ->orWhereNull('user_id');
                    }),
                $fromDateUTC,
                $toDateUTC,
                $colname
            )
        );

        if(!empty($salesTeams)){
            // sales rank
            $tmpSales = [];
            $tmpSalesLocal = [];
            $tmpSalesLong = [];

            $tmpEff = [];
            $tmpEffLocal = [];
            $tmpEffLong = [];

            foreach ($collection as $item) {
                // для команды local ранжируем по данным SalesFactCR
                if($item['type'] === ReportColumnEnum::SalesFactCR()){
                    foreach ($salesTeams['local'] ?? [] as $v){
                        $tmpSalesLocal[$v] = $item[$v];
                    }
                }
                // для команды long ранжируем по данным SuccessRevenue
                if($item['type'] === ReportColumnEnum::SuccessRevenue()){
                    foreach ($salesTeams['long'] ?? [] as $v){
                        $tmpSalesLong[$v] = $item[$v];
                    }
                }

                // для команды local ранжируем по данным ConversionFact
                if($item['type'] === ReportColumnEnum::ConversionFact()){
                    foreach ($salesTeams['local'] ?? [] as $v){
                        $tmpEffLocal[$v] = $item[$v];
                    }
                }
                // для команды long ранжируем по данным LeadsBookedSum
                if($item['type'] === ReportColumnEnum::LeadsBookedSum()){
                    foreach ($salesTeams['long'] ?? [] as $v){
                        $tmpEffLong[$v] = $item[$v];
                    }
                }
            }

            $tmpSales = array_merge_recursive(
                $this->rankUsers($tmpSalesLocal),
                $this->rankUsers($tmpSalesLong)
            );

            $collection['SalesRank'] = collect($collection['SalesRank'])
                ->mapWithKeys(function($value, $field) use ($tmpSales) {
                    if (str_starts_with($field, 'user_')) {
                        return [$field => $tmpSales[$field] ?? ""];
                    }
                    return [$field => $value];
                })->all();

            $tmpEff = array_merge_recursive(
                $this->rankUsers($tmpEffLocal),
                $this->rankUsers($tmpEffLong)
            );
            $collection['EfficiencyRank'] = collect($collection['EfficiencyRank'])
                ->mapWithKeys(function($value, $field) use ($tmpEff) {
                    if (str_starts_with($field, 'user_')) {
                        return [$field => $tmpEff[$field] ?? ""];
                    }
                    return [$field => $value];
                })->all();
        }

        return $collection;
    }

    private function getPeriodReport(
        Builder $builder,
        CarbonImmutable $fromDateUTC,
        CarbonImmutable $toDateUTC,
        $colname,
        ?User $user = null
    )
    {
        $bookedStatuses = [Order\Status::BOOKED_ID];
        $successStatuses = [
            Order\Status::SALES_DONE_ID,
            Order\Status::SUCCESS_ID
        ];
        $validated = request()->all();

        $leadsTotal = $builder
            ->clone()
            ->where(function ($q) {
                $q->where('status_id', Order\Status::NEW_LEAD_ID)
                    ->orWhereHas('statusHistory', function ($q) {
                        $q->where('prev_status', Order\Status::NEW_LEAD_ID);
                    });
            })
            ->whereBetween('created_at', [$fromDateUTC, $toDateUTC])
            ->count();

        $leadsLost = $builder
            ->clone()
            ->with('extended')
            ->where('status_id', Order\Status::LOST_ID)
            ->when($validated['filter']['period-type'] == 'by_status_changed',
                function ($query) use ($fromDateUTC, $toDateUTC) {
                    return $query->whereHas('statusHistoryLatest',
                        function (Builder $q) use ($fromDateUTC, $toDateUTC) {
                            $q->where('new_status', Order\Status::LOST_ID)
                            ->whereBetween('created_at', [$fromDateUTC, $toDateUTC]);
                    });
            })
            ->count();

        $leadsCalculated = $builder
            ->clone()
            ->with(['statusHistory', 'calculated'])
            ->when($validated['filter']['period-type'] == 'by_creation',
                function ($query) {
                    return $query->whereHas('statusHistory',
                        function (Builder $q) {
                            $q->whereIn('new_status', [Order\Status::CALCULATED_DONE_ID]);
                    });
            })
            ->when($validated['filter']['period-type'] == 'by_status_changed',
                function ($query) use ($fromDateUTC, $toDateUTC) {
                    return $query->whereHas('statusHistory',
                        function (Builder $q) use ($fromDateUTC, $toDateUTC) {
                            $q->where('new_status', Order\Status::CALCULATED_DONE_ID)
                            ->whereBetween('created_at', [$fromDateUTC, $toDateUTC]);
                });
            })
            ->get();

        $leadsCalculatedEstimatedSum = 0;
        if ($leadsCalculated->isNotEmpty()) {
            $i = 0;
            foreach ($leadsCalculated as $order) {
                /** @var $order Order */
                $total = $order->calculated->where('title', 'total');
                if ($total->isNotEmpty()) {
                    $total = $total->first();
                    $value = preg_replace('/(.*?-\s)?\$/m', '', $total->value);
                    $value = preg_replace('/\,/m', '', $value);
                    $leadsCalculatedEstimatedSum += +$value;
                }
                $i++;
            }
        }

        $leadsBookedCollection = $builder
            ->clone()
            ->with(['statusHistory', 'calculated'])
            ->when($validated['filter']['period-type'] == 'by_creation',
                function ($query) use ($bookedStatuses) {
                    return $query->whereHas('statusHistory',
                        function (Builder $q) use ($bookedStatuses) {
                            $q->whereIn('new_status', $bookedStatuses);
                    });
            })
            ->when($validated['filter']['period-type'] == 'by_status_changed',
                function ($query) use ($fromDateUTC, $toDateUTC, $bookedStatuses) {
                    return $query->whereHas('statusHistory',
                        function (Builder $q) use ($fromDateUTC, $toDateUTC, $bookedStatuses) {
                            $q->whereIn('new_status', $bookedStatuses)
                            ->whereBetween('created_at', [$fromDateUTC, $toDateUTC]);
                    });
            })
            ->get();

        $leadsBookedSum = 0;
        $leadsBooked = $leadsBookedCollection->count();
        if ($leadsBookedCollection->isNotEmpty()) {
            foreach ($leadsBookedCollection as $order) {
                /** @var $order Order */
                $total = $order->calculated->where('title', 'total');
                if ($total->isNotEmpty()) {
                    $total = $total->first();
                    $value = preg_replace('/(.*?-\s)?\$/m', '', $total->value);
                    $value = preg_replace('/\,/m', '', $value);
                    $leadsBookedSum += +$value;
                }
            }
        }

        $leadsBookedFromCalculatedCollection = $builder
            ->clone()
            ->with(['statusHistory', 'calculated'])
            ->when($validated['filter']['period-type'] == 'by_creation',
                function ($query) use ($bookedStatuses) {
                    return $query->whereHas('statusHistory',
                        function (Builder $q) use ($bookedStatuses) {
                            $q->whereIn('new_status', $bookedStatuses)
                                ->where('prev_status', Order\Status::CALCULATED_DONE_ID);
                        });
                })
            ->when($validated['filter']['period-type'] == 'by_status_changed',
                function ($query) use ($fromDateUTC, $toDateUTC, $bookedStatuses) {
                    return $query->whereHas('statusHistory',
                        function (Builder $q) use ($fromDateUTC, $toDateUTC, $bookedStatuses) {
                            $q->whereIn('new_status', $bookedStatuses)
                                ->where('prev_status', Order\Status::CALCULATED_DONE_ID)
                                ->whereBetween('created_at', [$fromDateUTC, $toDateUTC]);
                        });
                })
            ->get();

        $leadsBookedFromCalculatedSum = 0;
        $leadsBookedFromCalculated = $leadsBookedFromCalculatedCollection->count();
        if ($leadsBookedFromCalculatedCollection->isNotEmpty()) {
            foreach ($leadsBookedFromCalculatedCollection as $order) {
                /** @var $order Order */
                $total = $order->calculated->where('title', 'total');
                if ($total->isNotEmpty()) {
                    $total = $total->first();
                    $value = preg_replace('/(.*?-\s)?\$/m', '', $total->value);
                    $value = preg_replace('/\,/m', '', $value);
                    $leadsBookedFromCalculatedSum += +$value;
                }
            }
        }

        $leadsSuccess = $builder
            ->clone()
            ->with('payments')
            ->with('statusHistoryLatest')
            ->whereIn('status_id', $successStatuses)
            ->when($validated['filter']['period-type'] == 'by_status_changed',
                function ($query) use ($fromDateUTC, $toDateUTC, $successStatuses) {
                    return $query->whereHas('statusHistoryLatest',
                        function (Builder $q) use ($fromDateUTC, $toDateUTC, $successStatuses) {
                    $q->whereIn('new_status', $successStatuses)
                        ->whereBetween('created_at', [$fromDateUTC, $toDateUTC]);
                });
            })
            ->get();

        $successRevenue = 0;
        $successAOV = 0;
        if ($leadsSuccess->isNotEmpty()) {
            foreach ($leadsSuccess as $k => $order) {
                /** @var $order Order */
                if ($order->payments) {
                    $successRevenue += $order->payments->reduce(function (?float $carry, $item) {
                        return $carry + $item->amount;
                    });
                }
            }

            $successAOV = round($successRevenue / $leadsSuccess->count(), 2);
            $successRevenue = round($successRevenue, 2);
        }

        $leadsDuplicateCount = $builder
            ->clone()
//            ->where('status_id', Order\Status::DUPLICATE_ID)
            ->when($validated['filter']['period-type'] == 'by_creation',
                function ($query) {
                    return $query->whereHas('statusHistory',
                        function (Builder $q) {
                            $q->where('new_status', Order\Status::DUPLICATE_ID);
                        });
                })
            ->when($validated['filter']['period-type'] == 'by_status_changed',
                function ($query) use ($fromDateUTC, $toDateUTC) {
                    return $query->whereHas('statusHistoryLatest',
                        function (Builder $q) use ($fromDateUTC, $toDateUTC) {
                            $q->where('new_status', Order\Status::DUPLICATE_ID)
                                ->whereBetween('created_at', [$fromDateUTC, $toDateUTC]);
                        });
                })
            ->count();

        $leadsBadZipCount = $builder
            ->clone()
            ->where('status_id', Order\Status::LOST_ID)
            ->whereHas('tags', function (Builder $q){
                $q->where(Order\Tag::TABLE.'.id', Order\Tag::BAD_ZIP_ID);
            })
            ->whereDoesntHave('tags', function (Builder $q) {
                $q->where(Order\Tag::TABLE.'.id', Order\Tag::CANT_SERVICE_ID);
            })
            ->count()
        ;

        $leadsCantServiceCount = $builder
            ->clone()
            ->where('status_id', Order\Status::LOST_ID)
            ->whereHas('tags', function (Builder $q){
                $q->where(Order\Tag::TABLE.'.id', Order\Tag::CANT_SERVICE_ID);
            })
            ->count();

        $salesPlan = "";
        if(
            $user
            && $user->employee
            && $user->employee->salesPlans->isNotEmpty()
        ){
            if(!empty($this->months)){
                $tmpSalesPlanMoths = [];
                foreach ($this->months as $month){
                    $tmpSalesPlanMoths[] = EfficiencyPlan::validDate($month);
                }

                $plans = $user
                    ->employee
                    ->salesPlans()
                    ->whereIn('date_at', $tmpSalesPlanMoths)
                    ->get();

                if($user->employee->sales_team?->isLocal()){
                    $salesPlan = $plans->sum('local');
                } else {
                    $salesPlan = $plans->sum('intrestate');
                }

            } else {
                /** @var $plan SalesPlan */
                if(
                    $plan = $user
                        ->employee
                        ->salesPlans
                        ->where('date_at', EfficiencyPlan::validDate($this->targetDate_YM))
                        ->first()
                ){
                    if($user->employee->sales_team?->isLocal()){
                        $salesPlan = $plan->local;
                    } else {
                        $salesPlan = $plan->intrestate;
                    }
                }
            }
        }


//        if($user && $user->employee && $user->employee->salesPlans->isNotEmpty()){
//            /** @var $plan SalesPlan */
//            if(
//                $plan = $user
//                    ->employee
//                    ->salesPlans
//                    ->where('date_at', EfficiencyPlan::validDate($this->targetDate_YM))
//                    ->first()
//            ){
//                if($user->employee->sales_team?->isLocal()){
//                    $salesPlan = $plan->local;
//                } else {
//                    $salesPlan = $plan->intrestate;
//                }
//            }
//        }

        $salesPlanQty = 0;
        if($salesPlan && $successAOV){
            $salesPlanQty = round($salesPlan / $successAOV, 2);
        }

        $conversionPlan = "";
        if(
            $user
            && $user->employee
        ){
            if(!empty($this->months)){

                $tmpEfficiencyPlanMoths = [];
                foreach ($this->months as $month){
                    $tmpEfficiencyPlanMoths[] = EfficiencyPlan::validDate($month);
                }

                $efficiencyPlans = EfficiencyPlan::query()
                    ->whereIn('date_at', $tmpEfficiencyPlanMoths)
                    ->get();

                /** @var $efficiencyPlan EfficiencyPlan */
                if($user->employee->sales_team?->isLocal()){
                    $conversionPlan = $efficiencyPlans->sum('conversion_local_team');
                } else {
                    $conversionPlan = $efficiencyPlans->sum('conversion_long_team');
                }


            } else {
                if(
                    $efficiencyPlan = EfficiencyPlan::query()
                        ->where('date_at', EfficiencyPlan::validDate($this->targetDate_YM))
                        ->first()
                ){
                    /** @var $efficiencyPlan EfficiencyPlan */
                    if($user->employee->sales_team?->isLocal()){
                        $conversionPlan = $efficiencyPlan->conversion_local_team;
                    } else {
                        $conversionPlan = $efficiencyPlan->conversion_long_team;
                    }
                }
            }
        }
//        if(
//            $user
//            && $user->employee
//            && $efficiencyPlan = EfficiencyPlan::query()
//                ->where('date_at', EfficiencyPlan::validDate($this->targetDate_YM))
//                ->first()
//        ){
//            /** @var $efficiencyPlan EfficiencyPlan */
//            if($user->employee->sales_team?->isLocal()){
//                $conversionPlan = $efficiencyPlan->conversion_local_team;
//            } else {
//                $conversionPlan = $efficiencyPlan->conversion_long_team;
//            }
//        }

        $conversionFact = $this->conversionCalculation(
            $leadsBooked,
            $leadsBadZipCount,
            $leadsCantServiceCount,
            $leadsDuplicateCount,
            $leadsTotal,
        );

        $salesFact = "";
        if($successRevenue && $salesPlan){
            $salesFact = round(
                ($successRevenue/$salesPlan) *100,
                2
            );
        }

        $left = "";
        if($salesPlan && $successRevenue){
            $left = $salesPlan - $successRevenue - $leadsBookedSum;
        }
        $leftQty = 0;
        if($salesPlanQty && $leadsSuccess->count()){
            $leftQty = round($salesPlanQty - $leadsSuccess->count(), 2);
        }

        return [
            ReportColumnEnum::LeadsTotal() => [
                $colname => empty($leadsTotal) ? '' : $leadsTotal
            ],
            ReportColumnEnum::LeadsLost() => [
                $colname => empty($leadsLost) ? '' : $leadsLost
            ],
            ReportColumnEnum::LeadsLostCR() => [
                $colname => !empty($leadsTotal) ? round(100 * $leadsLost / $leadsTotal, 2) . '%' : ''
            ],
            ReportColumnEnum::LeadsCalculated() => [
                $colname => $leadsCalculated->isEmpty() ? '' : $leadsCalculated->count()
            ],
            ReportColumnEnum::LeadsCalculatedCR() => [
                $colname => !empty($leadsTotal) ? round(100 * $leadsCalculated->count() / $leadsTotal, 2) . '%' : ''
            ],
            ReportColumnEnum::LeadsCalculatedSum() => [
                $colname => !empty($leadsCalculatedEstimatedSum) ? '$' . to_int($leadsCalculatedEstimatedSum) : ''
            ],
            ReportColumnEnum::LeadsBooked() => [
                $colname => empty($leadsBooked) ? '' : $leadsBooked
            ],
            ReportColumnEnum::LeadsBookedCR() => [
                $colname => !empty($leadsTotal) ? round(100 * $leadsBooked / $leadsTotal, 2) . '%' : ''
            ],
            ReportColumnEnum::LeadsBookedSum() => [
                $colname => !empty($leadsBookedSum) ? '$' . to_int($leadsBookedSum) : ''
            ],
            ReportColumnEnum::LeadsBookedFromCalculated() => [
                $colname => empty($leadsBookedFromCalculated) ? '' : $leadsBookedFromCalculated
            ],
            ReportColumnEnum::LeadsBookedFromCalculatedCR() => [
                $colname => !empty($leadsTotal) ? round(100 * $leadsBookedFromCalculated / $leadsTotal, 2) . '%' : ''
            ],
            ReportColumnEnum::LeadsBookedFromCalculatedSum() => [
                $colname => !empty($leadsBookedFromCalculatedSum) ? '$' . to_int($leadsBookedFromCalculatedSum) : ''
            ],
            ReportColumnEnum::LeadsSuccessful() => [
                $colname => $leadsSuccess->isEmpty() ? '' : $leadsSuccess->count()
            ],
            ReportColumnEnum::LeadsSuccessfulCR() => [
                $colname => !empty($leadsTotal) ? round(100 * $leadsSuccess->count() / $leadsTotal, 2) . '%' : ''
            ],
            ReportColumnEnum::SuccessRevenue() => [
                $colname => $this->responseAsSum($successRevenue)
            ],
            ReportColumnEnum::SuccessAOV() => [
                $colname => $this->responseAsSum($successAOV)
            ],
            ReportColumnEnum::LeadsDuplicate() => [
                $colname => $this->responseAsCount($leadsDuplicateCount)
            ],
            ReportColumnEnum::LeadsBadZip() => [
                $colname => $this->responseAsCount($leadsBadZipCount)
            ],
            ReportColumnEnum::LeadsCantService() => [
                $colname => $this->responseAsCount($leadsCantServiceCount)
            ],
            ReportColumnEnum::SalesPlan() => [
                $colname =>  $this->responseAsSum($salesPlan)
            ],
            ReportColumnEnum::SalesPlanQty() => [
                $colname =>  $this->responseAsCount($salesPlanQty)
            ],
            ReportColumnEnum::Left() => [
                $colname => $this->responseAsSum($left)
            ],
            ReportColumnEnum::LeftQty() => [
                $colname => $this->responseAsCount($leftQty)
            ],
            ReportColumnEnum::SalesFactCR() => [
                $colname => $this->responseAsPercent($salesFact)
            ],
            ReportColumnEnum::SalesRank() => [
                $colname => ''
            ],
            ReportColumnEnum::ConversionPlan() => [
                $colname => $this->responseAsPercent($conversionPlan)
            ],
            ReportColumnEnum::ConversionFact() => [
                $colname => $this->responseAsPercent($conversionFact)
            ],
            ReportColumnEnum::EfficiencyRank() => [
                $colname => ''
            ],
        ];
    }

    public function rankUsers(array $data)
    {
        // Исключите все несвязанные элементы
        $filtered = array_filter($data, function($k) use ($data) {
                return str_starts_with($k, 'user_') && $data[$k] !== "";
            }, ARRAY_FILTER_USE_KEY
        );

        // Удаляем знак процента и преобразуем значение в целое число
        foreach ($filtered as $k => $v) {
            $filtered[$k] = (float)str_replace(['%', '$'], '', $v);
        }

        // Проводим сортировку в порядке убывания
        arsort($filtered);

        // Присваиваем ранги
        $rank = 1;
        $prevScore = NULL;
        foreach ($filtered as &$score) {
            if ($score !== $prevScore) { // Если у нас новый балл
                $prevScore = $score;
                $rank++;
            }
            $score = $rank - 1; // Присваиваем текущий ранг
        }

        // Добавляем пустые значения обратно и сортируем по ключам
        $result = $filtered + $data;
        ksort($result);

        return $result;
    }

    public static function getUsersNameForHeader(array $data): array
    {
        $first = current($data);

        $users = [];
        array_map(function ($key) use (&$users) {
            if(strpos($key, 'user') === 0){
                $users[$key] = last(explode('_', $key));
            }
        }, array_keys($first));

        User::query()
            ->whereIn('id', array_values($users))
            ->each(function ($user) use (&$users) {
                $users['user_'.$user->id] = $user->name;
            })
        ;
        $users['user_0'] = 'Without manager';

        return array_merge(['Field name'], array_values($users));
    }

    private function responseAsCount(int|float $value): string|int|float
    {
        if($value == 0){
            return '';
        }
        return $value;
    }

    private function responseAsPercent(null|int|float|string $value): string
    {
        if(is_null($value) || $value == ""){
            return '';
        }
        return $value . '%';
    }

    private function responseAsSum(null|int|float|string $value): string
    {
        if(is_null($value) || $value == "" || empty($value)){
            return '';
        }

        return '$'. to_int($value) ;
    }

    private function conversionCalculation(
        int $bookedCount,
        int $zipCodeCount,
        int $cantServiceCount,
        int $duplicateCount,
        int $leadCount,
    ): null|int|float
    {
        if($leadCount == 0){
            return null;
        }

        if(($leadCount - $duplicateCount - $zipCodeCount - $cantServiceCount) == 0){
            return 0;
        }

        $result = ($bookedCount/($leadCount - $duplicateCount - $zipCodeCount - $cantServiceCount))*100;

        return round($result,2);
    }

    private function aggregateCount(array $data): array
    {
        $tmp = $this->userData($data);

        foreach ($tmp as $user => $i) {
            $sum = null;
            foreach ($i as $v) {
                if($v !== ""){
                    $sum += $v;
                }
            }
            $tmp[$user] = $sum;
        }

        $result = current($data);

        foreach ($tmp as $user => $v) {
            $result[$user] = !is_null($v) ? $v : "";
        }

        return $result;
    }

    private function aggregateCountAverage(array $data): array
    {
        $tmp = $this->userData($data);

        foreach ($tmp as $user => $i) {
            $t = [];
            foreach ($i as $v) {
                if($v !== ""){
                    $t[] = $v;
                }
            }

            $tmp[$user] = !empty($t)
                ? round(array_sum($t)/count($t), 2)
                : null
            ;
        }

        $result = current($data);
        foreach ($tmp as $user => $v) {
            $result[$user] = !is_null($v) ? $v : "";
        }

        return $result;
    }

    private function aggregatePercent(array $data): array
    {
        $tmp = $this->userData($data);

        foreach ($tmp as $user => $i) {
            $t = [];
            foreach ($i as $v) {
                if($v !== ""){
                    $t[] = floatval(str_replace('%', '', $v));
                }
            }

            $tmp[$user] = !empty($t)
                ? round(array_sum($t)/count($t), 2)
                : null
            ;
        }

        $result = current($data);
        foreach ($tmp as $user => $v) {
            $result[$user] = !is_null($v)
                ? $v.'%'
                : "";
        }

        return $result;
    }

    private function aggregateSum(array $data): array
    {
        $tmp = $this->userData($data);

        foreach ($tmp as $user => $i) {
            $sum = null;
            foreach ($i as $v) {
                if($v !== ""){
                    $sum += floatval(str_replace('$', '', $v));
                }
            }

            $tmp[$user] = $sum;
        }

        $result = current($data);
        foreach ($tmp as $user => $v) {
            $result[$user] = !is_null($v)
                ? '$'.$v
                : ""
            ;
        }

        return $result;
    }
}

