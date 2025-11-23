<?php

namespace WezomCms\Imports\Repositories;

use Illuminate\Database\Eloquent\Builder;
use WezomCms\Core\Repositories\AbstractRepository;
use WezomCms\Imports\Models\Import;

class ImportRepository extends AbstractRepository
{
    protected function query(): Builder
    {
        return Import::query();
    }

    public function getLastRow(): ?Import
    {
        return $this->query()
            ->latest('id')
            ->first();
    }
}
