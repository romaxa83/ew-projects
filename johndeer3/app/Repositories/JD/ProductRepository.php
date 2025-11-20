<?php

namespace App\Repositories\JD;

use App\Abstractions\AbstractRepository;
use App\Models\JD\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductRepository extends AbstractRepository
{
    public function query(): Builder
    {
        return Product::query();
    }

    public function getSizeForSelect(): array
    {
        return $this->query()
            ->select('size_name')
            ->groupBy('size_name')
            ->distinct()
            ->toBase()
            ->get()
            ->pluck('size_name', 'size_name')
            ->toArray()
        ;
    }
}

