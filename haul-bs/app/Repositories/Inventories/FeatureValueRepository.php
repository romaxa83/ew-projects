<?php

namespace App\Repositories\Inventories;

use App\Foundations\Repositories\BaseEloquentRepository;
use App\Models\Inventories\Features\Value;

final readonly class FeatureValueRepository extends BaseEloquentRepository
{
    protected function modelClass(): string
    {
        return Value::class;
    }
}
