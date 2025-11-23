<?php

namespace WezomCms\Catalog\Repositories;

use Illuminate\Database\Eloquent\Builder;
use WezomCms\Catalog\Models\Brand;
use WezomCms\Core\Repositories\AbstractRepository;

class BrandRepository extends AbstractRepository
{
    protected function query(): Builder
    {
        return Brand::query();
    }
}

