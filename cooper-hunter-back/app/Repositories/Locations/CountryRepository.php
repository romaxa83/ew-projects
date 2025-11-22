<?php

namespace App\Repositories\Locations;

use App\Models\Locations\Country;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class CountryRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return Country::query();
    }

    public function listForFront(
        array $filters,
        array $relations,
    ): Collection
    {
        return Country::query()
            ->with($relations)
            ->filter($filters)
            ->latest('sort')
            ->get();
    }
}
