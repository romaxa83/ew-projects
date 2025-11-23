<?php

namespace WezomCms\Catalog\Repositories;

use Illuminate\Database\Eloquent\Builder;
use WezomCms\Catalog\Models\Labels\Label;
use WezomCms\Core\Repositories\AbstractRepository;

class LabelRepository extends AbstractRepository
{
    protected function query(): Builder
    {
        return Label::query();
    }

    public function existByName(string $name): bool
    {
        return $this->query()
            ->whereHas('translations', function ($q) use($name){
                $q->where('name', $name);
            })
            ->exists();
    }
}

