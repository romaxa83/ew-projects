<?php

namespace App\Http\Controllers\Reports;

use App\Enums\Employee\SalesTeamEnum;
use App\Enums\Reports\ReportColumnEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\SalesFunelFilterRequest;
use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\{JsonResponse};

class SalesFunelReportController extends Controller
{

    /**
     * Get View List of Transactions.
     * @return Renderable
     */
    public function index(): Renderable
    {
        return view('layouts.render.with-container', [
            'component' => 'report-sales-funel-order',
            'title' => 'Sales funel order',
            'breadcrumbs' => []
        ]);
    }

    /**
     * test @see \Tests\Feature\Reports\SalesFunel\ReportTest
     */
    public function report(SalesFunelFilterRequest $request): JsonResponse
    {
        $division = session()->get('division');

        $statuses = $this->getStatuses();

        $filter = $request->validated();
        $filter['division_id'] = $division['id'];
        $filter['division_tz'] = $division['miscs']['tz'];

        if(!isset($filter['date_start'])){
            $now = CarbonImmutable::now();
            $filter['date_start'] = $now->startOfDay()->format('Y-m-d');
            $filter['date_end'] = $now->endOfDay()->format('Y-m-d');
        }

        $orderBuilder = Order::query()
            ->with([
                'statusHistory',
                'payments',
                'calculated',
                'manager.employee'
            ])
            ->where('division_id', $filter['division_id'])
            ->when(isset($filter['user_id']), function (Builder $builder) use ($filter) {
                $builder->where('user_id', $filter['user_id']);
            })
            ->when(isset($filter['sales_team']), function (Builder $builder) use ($filter) {
                if(
                    $filter['sales_team'] == SalesTeamEnum::Local()
                    || $filter['sales_team'] == SalesTeamEnum::Local_long()
                ){
                    return $builder->whereHas('manager', function ($query) use ($filter) {
                        $query->whereHas('employee', function ($query) use ($filter) {
                            $query->where('sales_team', $filter['sales_team']);
                        });
                    });
                }
                if($filter['sales_team'] == 'na'){
                    return $builder->whereHas('manager', function ($query) use ($filter) {
                        $query->whereHas('employee', function ($query) use ($filter) {
                            $query->whereNull('sales_team');
                        });
                    });
                }
            })
        ;

        $fromDateUTC = CarbonImmutable::createFromFormat('Y-m-d', $filter['date_start'], $filter['division_tz'])
            ->startOfDay()->setTimezone(new \DateTimeZone('UTC'));
        $toDateUTC = CarbonImmutable::createFromFormat('Y-m-d', $filter['date_end'], $filter['division_tz'])
            ->endOfDay()->setTimezone(new \DateTimeZone('UTC'));

//        dd($fromDateUTC, $toDateUTC);

        $newLeadCount = $orderBuilder
            ->whereHas('statusHistory', function (Builder $query) use ($fromDateUTC, $toDateUTC) {
                $query
                    ->where('new_status', Order\Status::NEW_LEAD_ID)
                    ->whereBetween('created_at', [$fromDateUTC, $toDateUTC])
                ;
            })
            ->count();

        $tmp = [
            ReportColumnEnum::LeadsQty->label() => [
                'title' => ReportColumnEnum::LeadsQty->label()
            ],
            ReportColumnEnum::LeadsSum->label() => [
                'title' => ReportColumnEnum::LeadsSum->label()
            ],
            ReportColumnEnum::LeadsCR->label() => [
                'title' => ReportColumnEnum::LeadsCR->label()
            ],
            ReportColumnEnum::LeadsLost->label() => [
                'title' => ReportColumnEnum::LeadsLost->label()
            ],
            ReportColumnEnum::LeadsLostSum->label() => [
                'title' => ReportColumnEnum::LeadsLostSum->label()
            ],
            ReportColumnEnum::LeadsLostCR->label() => [
                'title' => ReportColumnEnum::LeadsLostCR->label()
            ]
        ];

        foreach ($statuses as $name => $items) {
            $leads = $orderBuilder
                ->clone()
                ->whereHas('statusHistory', function (Builder $query) use ($items, $fromDateUTC, $toDateUTC) {
                    $query
                        ->whereIn('new_status', array_keys($items))
                        ->whereBetween('created_at', [$fromDateUTC, $toDateUTC])
                    ;
                })
                ->get()
            ;

            $lost = $orderBuilder
                ->clone()
                ->where('status_id', Order\Status::LOST_ID)
                ->whereHas('statusHistory', function (Builder $query) use ($items, $fromDateUTC, $toDateUTC) {
                    $query
                        ->whereIn('prev_status', array_keys($items))
                        ->whereBetween('created_at', [$fromDateUTC, $toDateUTC])
                    ;
                })
                ->get()
            ;

            $tmp[ReportColumnEnum::LeadsQty->label()][$name] = $leads->count();
            $tmp[ReportColumnEnum::LeadsSum->label()][$name] = '$'. $this->getSum($leads);
            $tmp[ReportColumnEnum::LeadsCR->label()][$name] =
                $newLeadCount == 0
                    ? '0%'
                    : round(($leads->count()/$newLeadCount) * 100, 2) . '%';
            $tmp[ReportColumnEnum::LeadsLost->label()][$name] = $lost->count();
            $tmp[ReportColumnEnum::LeadsLostSum->label()][$name] = '$'. $this->getSum($lost);
            $tmp[ReportColumnEnum::LeadsLostCR->label()][$name] =
                $newLeadCount == 0
                    ? '0%'
                    : round(($lost->count()/$newLeadCount) * 100, 2) . '%';

        }

        return response()
            ->json([
                'success' => true,
                'data' => [
                    'headers' => array_merge(['Metric'], array_keys($statuses)),
                    'records' => array_values($tmp)
                ],
            ]);
    }

    private function getSum(\Illuminate\Database\Eloquent\Collection $collection): float
    {
        $sum = 0;
        foreach ($collection as $lead) {
            /** @var $lead Order */
            if(
                $lead->status_id == Order\Status::SUCCESS_ID
                || $lead->status_id == Order\Status::SALES_DONE_ID
            ){
                $tmpSum = 0;

                if ($lead->payments) {
                    $tmpSum += $lead->payments->reduce(function (?float $carry, $item) {
                        return $carry + $item->amount;
                    });
                }
                $sum += $tmpSum;
            } else {
                $total = $lead->calculated->where('title', 'total');
                if ($total->isNotEmpty()) {
                    $total = $total->first();
                    $value = preg_replace('/(.*?-\s)?\$/m', '', $total->value);
                    $value = preg_replace('/\,/m', '', $value);
                    $sum += +$value;
                }
            }
        }

        return round($sum, 2);
    }

    public function getStatuses(): array
    {
        $result = [];
        Order\StatusGroup::query()
            ->with(['statuses'])
            ->where('in_funel_report', 1)
            ->orderBy('sort')
            ->each(function (Order\StatusGroup $model) use (&$result) {
                if($model->statuses->pluck('title', 'id')->isNotEmpty()){
                    $result[$model->title] = $model->statuses->pluck('title', 'id')->toArray();
                }
            });

        return $result;
    }

    public function salesTeam(): JsonResponse
    {
        $result = array_merge(
            [[
                'id' => 'all',
                'title' => 'All',
            ]],
            SalesTeamEnum::forSelect(),
            [[
                'id' => 'na',
                'title' => 'N/A',
            ]],
        );

        return response()
            ->json([
                'success' => true,
                'data' => $result
            ]);
    }

    public function reports(SalesFunelFilterRequest $request)
    {
        dd($request);
    }
}

