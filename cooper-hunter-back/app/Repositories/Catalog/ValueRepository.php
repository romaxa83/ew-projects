<?php

namespace App\Repositories\Catalog;

use App\Models\Catalog\Features\Value;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class ValueRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return Value::query();
    }
}
