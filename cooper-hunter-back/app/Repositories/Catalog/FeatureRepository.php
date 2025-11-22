<?php

namespace App\Repositories\Catalog;

use App\Models\Catalog\Features\Feature;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class FeatureRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return Feature::query();
    }
}

