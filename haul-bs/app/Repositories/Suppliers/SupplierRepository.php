<?php

namespace App\Repositories\Suppliers;

use App\Foundations\Repositories\BaseEloquentRepository;
use App\Models\Suppliers\Supplier;

final readonly class SupplierRepository extends BaseEloquentRepository
{
    protected function modelClass(): string
    {
        return Supplier::class;
    }
}
