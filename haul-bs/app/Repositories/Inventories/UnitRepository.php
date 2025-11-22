<?php

namespace App\Repositories\Inventories;

use App\Foundations\Repositories\BaseEloquentRepository;
use App\Models\Inventories\Unit;

final readonly class UnitRepository extends BaseEloquentRepository
{
    protected function modelClass(): string
    {
        return Unit::class;
    }
}
