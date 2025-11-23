<?php

namespace WezomCms\Catalog\Repositories;

use Illuminate\Database\Eloquent\Builder;
use WezomCms\Catalog\Models\Collections\Category;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use WezomCms\Core\Repositories\AbstractRepository;

class CollectionCategoryRepository extends AbstractRepository
{
    protected function query(): Builder
    {
        return Category::query();
    }

    public function getAllForFront(
        array $relations = [],
        array $filter = []
    ): EloquentCollection
    {
        return $this->query()
            ->sorting()
            ->with($relations)
            ->published()
            ->get();
    }

}

