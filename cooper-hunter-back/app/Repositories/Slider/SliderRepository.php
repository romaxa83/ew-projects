<?php

namespace App\Repositories\Slider;

use App\Models\Sliders\Slider;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class SliderRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return Slider::query();
    }

    public function getListWithSort(array $relations, array $filters): Collection
    {
        return $this->modelQuery()
            ->filter($filters)
            ->with($relations)
            ->latest('sort')
            ->get();
    }
}

