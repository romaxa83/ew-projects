<?php

namespace App\Repositories\Saas\GPS;

use App\Models\GPS\History;
use App\Models\Saas\GPS\Device;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

class HistoryRepository
{
    public function getBy(
        $field,
        $value,
        array $relations = [],
        $withException = false,
        $exceptionMessage = 'Model not found',
        $withoutId = null
    ): ?Model
    {
        $result = Device::query()
            ->with($relations)
            ->when($withoutId, fn(Builder $b): Builder => $b->whereNot('id', $withoutId))
            ->where($field, $value)
            ->first()
        ;

        if ($withException && null === $result) {
            throw new DomainException($exceptionMessage, Response::HTTP_NOT_FOUND);
        }

        return $result;
    }

    private function getByCompanyQuery(int $companyId, array $filter): Builder
    {
        return History::filter($filter)
            ->with([
                'driver',
                'oldDriver',
                'deviceWithTrashed',
                'trailer',
                'truck',
                'alerts',
                'alerts.trailer',
                'alerts.truck',
            ])
            ->where('company_id', $companyId)
//            ->orderByRaw("CASE event_type
//                WHEN 'idle' THEN 1
//                WHEN 'long_idle' THEN 2
//                WHEN 'engine_off' THEN 3
//                ELSE 4
//            END")
            ->orderBy('received_at', $filter['order_type'] ?? 'asc')
            ;
    }

    public function getByCompanyPagination(int $companyId, array $filter): LengthAwarePaginator
    {
        $data = History::filter($filter)
            ->with([
                'driver',
                'oldDriver',
                'deviceWithTrashed',
                'trailer',
                'truck',
                'alerts',
                'alerts.trailer',
                'alerts.truck',
            ])
            ->where('company_id', $companyId)
//            ->orderByRaw("CASE event_type
//                WHEN 'idle' THEN 1
//                WHEN 'long_idle' THEN 2
//                WHEN 'engine_off' THEN 3
//                ELSE 4
//            END")
            ->orderBy('received_at', $filter['order_type'] ?? 'desc')
            ->paginate(
                $filter['per_page'] ?? 50,
                ['*'],
                'page',
                $filter['page'] ?? 1
            );

        return $data;
    }

    public function getByCompanyCollection(int $companyId, array $filter): Collection
    {
        return $this->getByCompanyQuery($companyId, $filter)
            ->get();
    }
}


