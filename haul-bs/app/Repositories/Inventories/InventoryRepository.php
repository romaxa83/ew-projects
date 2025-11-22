<?php

namespace App\Repositories\Inventories;

use App\Foundations\Repositories\BaseEloquentRepository;
use App\Models\Inventories\Inventory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class InventoryRepository extends BaseEloquentRepository
{
    protected function modelClass(): string
    {
        return Inventory::class;
    }

    public function getCustomPagination(
        array $relations = [],
        array $filters = [],
    ): LengthAwarePaginator
    {
        return Inventory::query()
            ->with($relations)
            ->filter($filters)
            ->orderByRaw('CASE WHEN quantity = 0 THEN 0 ELSE 1 END desc, name asc')
            ->paginate(
                perPage: $this->getPerPage($filters),
                page: $this->getPage($filters)
            );
    }

    public function dataForExport(array $filters = []): Collection
    {
        return Inventory::query()
            ->with(['category'])
            ->filter($filters)
            ->orderByRaw('CASE WHEN quantity = 0 THEN 0 ELSE 1 END desc, name asc')
            ->get();
    }
}
