<?php

namespace App\Repositories\Vehicles;

use App\Foundations\Repositories\BaseEloquentRepository;
use App\Models\Vehicles\Make;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final readonly class MakeRepository extends BaseEloquentRepository
{
    protected function modelClass(): string
    {
        return Make::class;
    }

    public function listWithSort(array $filters = []): Collection
    {
        return Make::query()
            ->filter($filters)
            ->when(isset($filters['search']), fn(Builder $q) => $q->orderSearchWord($filters['search']))
            ->get();
    }
}
