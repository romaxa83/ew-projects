<?php

namespace WezomCms\Catalog\Repositories;

use Illuminate\Database\Eloquent\Builder;
use WezomCms\Catalog\Models\Specifications\Specification;
use WezomCms\Core\Repositories\AbstractRepository;

class SpecificationRepository extends AbstractRepository
{
    protected function query(): Builder
    {
        return Specification::query();
    }
}
