<?php

namespace App\Repositories\Vehicles;

use App\Foundations\Repositories\BaseEloquentRepository;
use App\Models\Vehicles\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final readonly class ModelRepository extends BaseEloquentRepository
{
    protected function modelClass(): string
    {
        return Model::class;
    }

    public function listWithSort(array $filters = []): Collection
    {
        return Model::filter($filters)
            ->selectRaw('MIN(id) as id, name')
            ->groupBy(Model::TABLE . '.name')
            ->when(isset($filters['search']), fn(Builder $q) => $q->orderSearchWord($filters['search']))
            ->get();
    }
}
