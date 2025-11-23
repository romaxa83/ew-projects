<?php

namespace WezomCms\Orders\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use WezomCms\Core\Repositories\AbstractRepository;
use WezomCms\Orders\Models\Delivery;

class DeliveriesRepository extends AbstractRepository
{
    protected function query(): Builder
    {
        return Delivery::query();
    }

    public function getAllForFront(): EloquentCollection
    {
        return $this->query()
            ->published()
            ->sorting()
            ->get();
    }
}

