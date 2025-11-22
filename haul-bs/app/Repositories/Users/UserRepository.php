<?php

namespace App\Repositories\Users;

use App\Foundations\Repositories\BaseEloquentRepository;
use App\Models\Users\User;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class UserRepository extends BaseEloquentRepository
{
    const DEFAULT_ORDER_BY = 'status';
    const DEFAULT_ORDER_TYPE = 'desc';

    protected function modelClass(): string
    {
        return User::class;
    }

    public function allPagination(
        array $filters = [],
        array $relations = [],
    ): LengthAwarePaginator
    {
        $q = User::query()
            ->withoutSuperAdmin()
            ->filter($filters)
        ;

        if(isset($filters['order_by'])){
            $filters['order_type'] = $filters['order_type'] ?? self::DEFAULT_ORDER_TYPE;
        } else {
            $filters['order_by'] = self::DEFAULT_ORDER_BY;
            $filters['order_type'] = self::DEFAULT_ORDER_TYPE;
        }

        if ($filters['order_by'] === 'full_name') {
            $q->orderByRaw('concat(first_name, \' \', last_name) ' . $filters['order_type']);
        } else {
            $q->orderBy($filters['order_by'], $filters['order_type']);
        }

        return $q->paginate(
                perPage: $this->getPerPage($filters),
                page: $this->getPage($filters)
            );
    }
}

