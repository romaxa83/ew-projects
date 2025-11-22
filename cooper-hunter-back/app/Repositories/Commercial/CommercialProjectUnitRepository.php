<?php

namespace App\Repositories\Commercial;

use App\Models\Commercial\CommercialProjectUnit;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class CommercialProjectUnitRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return CommercialProjectUnit::query();
    }
}
