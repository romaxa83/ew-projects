<?php

namespace App\Repositories\Report;

use App\Abstractions\AbstractRepository;
use App\DTO\Stats\StatsDto;
use App\Models\BaseModel;
use App\Models\Report\Report;
use App\Models\User\User;
use App\Type\ReportStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportRepository extends AbstractRepository
{
    public function query(): Builder
    {
        return Report::query();
    }

    public function getAllReport(
        $relations = [],
        $filters = [],
        $order = []
    )
    {
        $user = \Auth::user();

        $perPage = $filters['per_page'] ?? BaseModel::DEFAULT_PER_PAGE;

        $q = $this->query()
            ->with($relations)
            ->listFilter($user)
            ->filter($filters)
        ;

        if(!empty($order)){
            foreach ($order as $field => $type) {
                $q->orderBy($field, $type);
            }
        }

        return $q->paginate($perPage);
    }

    public function getAllReportForExcel(
        $relations = [],
        $filters = [],
        $order = [],
        $statuses = []
    )
    {
        $user = \Auth::user();

        $q = $this->query()
            ->with($relations)
            ->whereNotIn('status', $statuses)
            ->listFilter($user)
            ->filter($filters)
        ;

        if(!empty($order)){
            foreach ($order as $field => $type) {
                $q->orderBy($field, $type);
            }
        }

        return $q->get();
    }

    public function forMachineStats(StatsDto $dto): Collection
    {
        return $this->query()->with([
            'user',
            'user.dealer',
            'location',
            'reportMachines.equipmentGroup',
            'reportMachines.modelDescription',
            'features.feature',
            'features.value',
        ])
            ->whereIn('status' , ReportStatus::listForMachineStatistics())
            ->whereYear('created_at', $dto->year)
            ->whereHas('location', function (Builder $q) use($dto) {
                if(is_array($dto->country)){
                    return $q->whereIn('country', $dto->country);
                }
                return $q->where('country', $dto->country);
            })
            ->whereHas('user',function(Builder $q) use($dto) {
                if(is_array($dto->dealer)){
                    return $q->whereIn('dealer_id', $dto->dealer);
                }
                return $q->where('dealer_id', $dto->dealer);
            })
            ->whereHas('reportMachines', function(Builder $q) use($dto) {
                return $q->whereHas('equipmentGroup', function (Builder $q) use ($dto) {
                    return $q->where('id', $dto->eg);
                });
            })
            ->whereHas('reportMachines', function (Builder $q) use ($dto) {
                return $q->whereHas('modelDescription', function (Builder $q) use ($dto) {
                    if(is_array($dto->md)){
                        return $q->whereIn('id', $dto->md);
                    }
                    return $q->where('id', $dto->md);
                });
            })
            ->get()
        ;
    }

    public function getReportByFeatureAndStatus($featureId, $status): Collection
    {
        return $this->query()
            ->with(['features'])
            ->whereHas('features', function ($q) use ($featureId){
                $q->where('feature_id', $featureId);
            })
            ->where('status', $status)
            ->get();
    }

    public function getAllForSearch($filters): LengthAwarePaginator
    {
        $user = \Auth::user();

        return $this->query()
            ->with([
                'user',
                'user.profile',
                'user.dealer',
                'user.dealer.tm',
                'clients',
                'reportClients',
                'location',
                'reportMachines'
            ])
            ->listFilter($user)
            ->search($filters['search'])
            ->orderBy('id')
            ->paginate($this->getPerPage($filters))
        ;
    }

//    public function getForPush(
//        $sendPush = null,
//        $days = Report::DEFAULT_DAY_FOR_PUSH
//    )
//    {
//        $now = Carbon::now();
//        $future = Carbon::now()->addDays($days);
//
//        $query = $this->query()
//            ->whereHas('pushData', function ($q) use ($now, $future) {
//                $q->whereBetween('planned_at', [$now, $future]);
//            });
//
//        if(null !== $sendPush){
//            $query->whereHas('pushData', function ($q) use ($sendPush) {
//                $q->where('send_push', (bool)$sendPush);
//            });
//        }
//
//        return $query->get();
//    }

    public function getPushForWeek($isSend = null, $days = 7, $now = null)
    {
        $now = $now ?? Carbon::now();
        $future = Carbon::now()->addDays($days);

//        dd($now, $future);

        $query = $this->query()
            ->whereHas('pushData', function ($q) use ($now, $future) {
                $q->whereBetween('planned_at', [$now, $future]);
            });

        if(null !== $isSend){
            $query->whereHas('pushData', function ($q) use ($isSend) {
                $q->where('is_send_week', (bool)$isSend);
            });
        }

        return $query->get();
    }

    public function getPushStartDay($isSend = null, $hours = 39, $now = null)
    {
        $now = $now ??  Carbon::now();
        $future = Carbon::now()->addHours($hours);

        $query = $this->query()
            ->whereHas('pushData', function ($q) use ($now, $future) {
                $q->whereBetween('planned_at', [$now, $future]);
            });

        if(null !== $isSend){
            $query->whereHas('pushData', function ($q) use ($isSend) {
                $q->where('is_send_start_day', (bool)$isSend);
            });
        }

        return $query->get();
    }


    public function getPushEndDay($isSend = null, $hours = 30, $now = null)
    {
        $now = $now ??  Carbon::now();
        $future = Carbon::now()->addHours($hours);

        $query = $this->query()
            ->whereHas('pushData', function ($q) use ($now, $future) {
                $q->whereBetween('planned_at', [$now, $future]);
            });

        if(null !== $isSend){
            $query->whereHas('pushData', function ($q) use ($isSend) {
                $q->where('is_send_end_day', (bool)$isSend);
            });
        }

        return $query->get();
    }

//    public function getForPushBetweenHour($start, $end, $days): Collection
//    {
//        $now = Carbon::now();
//        $future = Carbon::now()->addDays($days);
//
//        return $this->query()
//            ->with(['pushData'])
//            ->whereHas('pushData', function ($q) use ($now, $future) {
//                $q->whereBetween('planned_at', [$now, $future]);
//            })
//            ->get()
//            ->filter(function($model) use ($start, $end, $now) {
//                $diff = $model->pushData->planned_at->diffInHours($now);
//                return $diff > $start && $diff < $end;
//            });
//    }

}
