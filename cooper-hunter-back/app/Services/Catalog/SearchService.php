<?php

namespace App\Services\Catalog;

use App\Dto\Catalog\SearchDto;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Products\Product;
use Illuminate\Support\Collection;

class SearchService
{
    public function search(SearchDto $dto): Collection
    {
        return Product::filter($dto->getFilter())
            ->where('active', true)
            ->get()
            ->toBase()
            ->merge(
                Category::filter($dto->getFilter())
                    ->where('active', true)
                    ->get()
            );
    }
}
