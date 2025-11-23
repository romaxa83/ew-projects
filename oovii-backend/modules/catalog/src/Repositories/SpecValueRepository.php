<?php

namespace WezomCms\Catalog\Repositories;

use Illuminate\Database\Eloquent\Builder;
use WezomCms\Catalog\Models\Specifications\SpecValue;
use WezomCms\Core\Repositories\AbstractRepository;

class SpecValueRepository extends AbstractRepository
{
    protected function query(): Builder
    {
        return SpecValue::query();
    }

    public function getByNameAndSpec($name, $specId)
    {
        return $this->query()
            ->with([])
            ->where('specification_id', $specId)
            ->whereHas("translations", function($q) use ($name) {
                $q->where("name", $name);
            })
            ->first();
    }
}
