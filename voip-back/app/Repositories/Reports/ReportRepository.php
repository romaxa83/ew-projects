<?php

namespace App\Repositories\Reports;

use App\Entities\Reports\ReportAdditionalEntity;
use App\Enums\Formats\DatetimeEnum;
use App\Enums\Reports\ReportStatus;
use App\Models\Employees\Employee;
use App\Models\Reports\Report;
use App\Models\Sips\Sip;
use App\Repositories\AbstractRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class ReportRepository extends AbstractRepository
{
    public function modelClass(): string
    {
        return Report::class;
    }

    // возвращает массив, где ключ - id отчета, номер sip пользователя - который привязан к этому отчету
    public function getReportIdAdnSipNumber(): array
    {
        return DB::table(Report::TABLE)
            ->select(
                Report::TABLE. '.employee_id as employee_id',
                Report::TABLE. '.id as report_id',
                Employee::TABLE. '.sip_id as sip_id',
                Sip::TABLE. '.number as sip_number',
            )
            ->join(Employee::TABLE, Report::TABLE.'.employee_id', '=', Employee::TABLE.'.id')
            ->join(Sip::TABLE, Employee::TABLE.'.sip_id', '=', Sip::TABLE.'.id')
            ->get()
            ->pluck('sip_number','report_id')
            ->toArray();
    }

    private function reportBuilder(
        array $relation = [],
        array $filters = [],
        string|array $sort = 'id'
    ): Builder
    {
        $filterItems = [];
        if(isset($filters['date_from'])){
            $filterItems['date_from'] = $filters['date_from'];
            unset($filters['date_from']);
        }
        if(isset($filters['date_to'])){
            $filterItems['date_to'] = $filters['date_to'];
            unset($filters['date_to']);
        }

        $query = Report::query();

        if(!empty($filterItems)){
            $query->with(
                [
                    'items' => function($q) use ($filterItems) {
                        if(isset($filterItems['date_from'])){
                            $fromDate = Carbon::createFromFormat(DatetimeEnum::DEFAULT_FORMAT, $filterItems['date_from']);
                            $q->where('call_at', '>=', $fromDate);
                        }
                        if(isset($filterItems['date_to'])){
                            $toDate = Carbon::createFromFormat(DatetimeEnum::DEFAULT_FORMAT, $filterItems['date_to']);
                            $q->where('call_at', '<=', $toDate);
                        }
                    },
                    'pauseItems' => function($q) use ($filterItems) {
                        if(isset($filterItems['date_from'])){
                            $fromDate = Carbon::createFromFormat(DatetimeEnum::DEFAULT_FORMAT, $filterItems['date_from']);
                            $q->where('pause_at', '>=', $fromDate);
                        }
                        if(isset($filterItems['date_to'])){
                            $toDate = Carbon::createFromFormat(DatetimeEnum::DEFAULT_FORMAT, $filterItems['date_to']);
                            $q->where('unpause_at', '<=', $toDate);
                        }
                    }
                ]
            );
        }


        $query->filter($filters);

        if(!isset($filters['sort'])){
            if(is_array($sort)){
                foreach ($sort as $field => $type) {
                    $query->orderBy($field, $type);
                }
            } else {
                $query->latest($sort);
            }
        }

        return $query;
    }

    public function getPagination(
        array $relation = [],
        array $filters = [],
        string|array $sort = 'id'
    ): LengthAwarePaginator
    {
        return $this->reportBuilder(
            $relation,
            $filters,
            $sort,
        )->paginate(
            perPage: $this->getPerPage($filters),
            page: $this->getPage($filters)
        );
    }

    public function getCollection(
        array $relation = [],
        array $filters = [],
        string|array $sort = 'id'
    ): Collection
    {
        return $this->reportBuilder(
            $relation,
            $filters,
            $sort,
        )->get();
    }

    public function getAdditionalData(
        array $filters = [],
    ): ReportAdditionalEntity
    {
        $tmp = [
            'total_calls' => 0,
            'total_answer_calls' => 0,
            'total_dropped_calls' => 0,
            'total_transfer_calls' => 0,
            'total_wait' => 0,
            'total_time' => 0,
            'total_pause' => 0,
            'total_pause_time' => 0,
        ];

        $this->reportBuilder(
            ['items', 'pauseItems'],
            $filters,
        )
            ->get()
            ->each(function(Report $model) use(&$tmp){
                $tmp['total_calls'] += $model->getCallsCount();
                $tmp['total_answer_calls'] += $model->getAnsweredCallsCount();
                $tmp['total_dropped_calls'] += $model->getDroppedCallsCount();
                $tmp['total_transfer_calls'] += $model->getTransferCallsCount();
                $tmp['total_wait'] += $model->getTotalWait();
                $tmp['total_time'] += $model->getTotalTime();
                $tmp['total_pause'] += $model->getPauseCount();
                $tmp['total_pause_time'] += $model->getTotalPauseTime();
            })
        ;

        return new ReportAdditionalEntity($tmp);
    }
}
