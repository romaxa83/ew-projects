<?php

namespace App\Repositories\Inventories;

use App\Foundations\Repositories\BaseEloquentRepository;
use App\Models\Inventories\Brand;

final readonly class BrandRepository extends BaseEloquentRepository
{
    protected function modelClass(): string
    {
        return Brand::class;
    }
}
