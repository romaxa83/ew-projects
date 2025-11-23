<?php

namespace App\Repositories\Reports;

use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class FinancialCheckRepository
{
    private function builder(
        array $filter = []
    ): Builder
    {
        $date = $filter['date'];
        $divisionId = $filter['division_id'];
        $divisionTz = $filter['division_tz'];

//        $monthStart = CarbonImmutable::createFromFormat('Y-m', $date, $divisionTz)
//            ->startOfMonth()->setTimezone(new \DateTimeZone('UTC'));
//        $monthEnd = CarbonImmutable::createFromFormat('Y-m', $date, $divisionTz)
//            ->endOfMonth()->setTimezone(new \DateTimeZone('UTC'));

        $monthStart = CarbonImmutable::createFromFormat('Y-m', $date)
            ->startOfMonth()->format('Y-m-d');
        $monthEnd = CarbonImmutable::createFromFormat('Y-m', $date)
            ->endOfMonth()->format('Y-m-d');

        return Order::query()
            ->withWorksFormat()
            ->with([
                'manager.employee',
                'payments',
                'workLatest',
            ])
            ->where('division_id', $divisionId)
            ->whereIn('status_id', [
                Order\Status::SALES_DONE_ID,
                Order\Status::SUCCESS_ID,
            ])
            ->when(isset($filter['user_id']), function (Builder $builder) use ($filter) {
                $builder->where('user_id', $filter['user_id']);
            })
            ->whereHas('workLatest', function (Builder $builder) use ($monthStart, $monthEnd) {
                $builder->whereBetween('start_date', [$monthStart, $monthEnd]);
            })
            ->orderBy(
                Order\Work::select('start_date')
                ->whereColumn(Order\Work::TABLE.'.order_id', Order::TABLE.'.id')
                ->latest('start_date')
                ->take(1),
                'asc'
            )
            ;
    }

    public function getCollection(
        array $filter = []
    ): EloquentCollection
    {
        return $this->builder($filter)->get();
    }

    public function getPagination(
        array $filter = []
    ): LengthAwarePaginator
    {
        $result = $this->builder($filter)->paginate(
            perPage: 20,
            page: $filter['page'] ?? 1
        );

        $items = $result->getCollection();

        $transformedItems = $items->map(function (Order $model) {
            return $this->transformData($model);
        });

        return new LengthAwarePaginator(
            $transformedItems,
            $result->total(),
            $result->perPage(),
            $result->currentPage(),
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
            ]
        );
    }

    public function getCollectionForExport(
        array $filter = []
    ): Collection
    {
        $result = collect();

        $this->getCollection($filter)
            ->each(function (Order $model) use (&$result) {
                $result->push($this->transformData($model));
            });

        return $result;
    }

    private function transformData(Order $model): array
    {

        $serviceDate = null;
        if($model->works->isNotEmpty()){
            $first = $model->workLatest;
            $serviceDate = $first->start_date . ' ' . $first->start_time;
        }

        $total = 0;

        if ($model->payments) {
            $total += $model->payments->reduce(function (?float $carry, $item) {
                return $carry + $item->amount;
            });
        }

        return [
            'order_id' => $model->id,
            'manager_name' => $model->manager?->name,
            'service_date' => $serviceDate,
            'total_paid' => '$'.$total,
        ];

    }
}

