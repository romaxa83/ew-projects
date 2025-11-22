<?php

namespace App\Repositories\TypeOfWorks;

use App\Foundations\Repositories\BaseEloquentRepository;
use App\Models\TypeOfWorks\TypeOfWork;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class TypeOfWorkRepository extends BaseEloquentRepository
{
    const DEFAULT_ORDER_BY = 'name';
    const DEFAULT_ORDER_TYPE = 'asc';

    protected function modelClass(): string
    {
        return TypeOfWork::class;
    }

    public function customPagination(
        array $filters = [],
        array $relations = [],
    ): LengthAwarePaginator
    {
        $q = TypeOfWork::query()
            ->with($relations)
            ->filter($filters)
        ;

        if(isset($filters['order_by'])){
            $filters['order_type'] = $filters['order_type'] ?? self::DEFAULT_ORDER_TYPE;
        } else {
            $filters['order_by'] = self::DEFAULT_ORDER_BY;
            $filters['order_type'] = $filters['order_type'] ?? self::DEFAULT_ORDER_TYPE;
        }
        $q->orderBy($filters['order_by'], $filters['order_type']);

        return $q->paginate(
            perPage: $this->getPerPage($filters),
            page: $this->getPage($filters)
        );
    }
}
