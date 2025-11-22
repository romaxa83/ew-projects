<?php

namespace App\Repositories\Inventories;

use App\Foundations\Repositories\BaseEloquentRepository;
use App\Models\Inventories\Features\Feature;

final readonly class FeatureRepository extends BaseEloquentRepository
{
    protected function modelClass(): string
    {
        return Feature::class;
    }
}
