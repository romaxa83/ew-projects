<?php

namespace App\Repositories\Vehicles;

use App\Foundations\Models\BaseModel;
use App\Foundations\Repositories\BaseEloquentRepository;
use App\Models\Companies\Company;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Illuminate\Database\Eloquent\Collection;

final readonly class TruckRepository extends BaseEloquentRepository
{
    const DEFAULT_ORDER_BY = 'id';
    const DEFAULT_ORDER_TYPE = 'desc';

    protected function modelClass(): string
    {
        return Truck::class;
    }

    public function getById(int $id): BaseModel|Truck
    {
        return $this->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.vehicles.truck.not_found"));
    }

    public function getByOriginId(int $id): BaseModel|Truck
    {
        return $this->getBy(['origin_id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.vehicles.truck.not_found"));
    }

    public function customPagination(
        array $relation = [],
        array $filters = [],
    )
    {
        $q = Truck::query()
            ->filter($filters)
            ->with($relation);

        if(isset($filters['order_by'])){
            $filters['order_type'] = $filters['order_type'] ?? self::DEFAULT_ORDER_TYPE;
        } else {
            $filters['order_by'] = self::DEFAULT_ORDER_BY;
            $filters['order_type'] = self::DEFAULT_ORDER_TYPE;
        }

        if ($filters['order_by'] === 'company_name') {
            $q->select(Truck::TABLE.'.*', Company::TABLE.'.name')
                ->leftJoin(
                    Company::TABLE,
                    Truck::TABLE.'.company_id',
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

    public function getTrucksWithVin(string $vin, ?int $excludeId): Collection
    {
        $q = Truck::query()
            ->select(['id', 'make', 'model', 'unit_number'])
            ->where('vin', $vin);

        if ($excludeId) {
            $q->where('id', '!=', $excludeId);
        }

        return $q->get();
    }
}
