<?php

namespace WezomCms\Catalog\Repositories;

use Illuminate\Database\Eloquent\Builder;
use WezomCms\Catalog\Models\Category;
use WezomCms\Core\Repositories\AbstractRepository;

class CategoryRepository extends AbstractRepository
{
    protected function query(): Builder
    {
        return Category::query();
    }
}
