<?php

namespace App\Repositories\Vehicles;

use App\Foundations\Models\BaseModel;
use App\Foundations\Repositories\BaseEloquentRepository;
use App\Models\Companies\Company;
use App\Models\Vehicles\Trailer;
use Illuminate\Database\Eloquent\Collection;

final readonly class TrailerRepository extends BaseEloquentRepository
{
    const DEFAULT_ORDER_BY = 'id';
    const DEFAULT_ORDER_TYPE = 'desc';

    protected function modelClass(): string
    {
        return Trailer::class;
    }

    public function getById(int $id): BaseModel|Trailer
    {
        return $this->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.vehicles.trailer.not_found"));
    }

    public function getByOriginId(int $id): BaseModel|Trailer
    {
        return $this->getBy(['origin_id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.vehicles.trailer.not_found"));
    }

    public function customPagination(
        array $relation = [],
        array $filters = [],
    )
    {
        $q = Trailer::query()
            ->filter($filters)
            ->with($relation);

        if(isset($filters['order_by'])){
            $filters['order_type'] = $filters['order_type'] ?? self::DEFAULT_ORDER_TYPE;
        } else {
            $filters['order_by'] = self::DEFAULT_ORDER_BY;
            $filters['order_type'] = self::DEFAULT_ORDER_TYPE;
        }

        if ($filters['order_by'] === 'company_name') {
            $q->select(Trailer::TABLE.'.*', Company::TABLE.'.name')
                ->leftJoin(
                    Company::TABLE,
                    Trailer::TABLE.'.company_id',
                    '=',
                    Company::TABLE.'.id'
                )
                ->orderByRaw(Company::TABLE.'.id IS NOT NULL DESC')
                ->orderBy(Company::TABLE.'.name', $filters['order_type'])
            ;
        } else {
            $q->orderBy($filters['order_by'], $filters['order_type']);
        }

        return $q->paginate(
            perPage: $this->getPerPage($filters),
            page: $this->getPage($filters)
        );
    }
    public function getTrailerWithVin(string $vin, ?int $excludeId): Collection
    {
        $q = Trailer::query()
            ->select(['id', 'make', 'model', 'unit_number'])
            ->where('vin', $vin);

        if ($excludeId) {
            $q->where('id', '!=', $excludeId);
        }

        return $q->get();
    }
}

