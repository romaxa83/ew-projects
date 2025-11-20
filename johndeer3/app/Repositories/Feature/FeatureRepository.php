<?php

namespace App\Repositories\Feature;

use App\Abstractions\AbstractRepository;
use App\Models\Report\Feature\Feature;
use Illuminate\Database\Eloquent\Builder;

class FeatureRepository extends AbstractRepository
{
    public function query(): Builder
    {
        return Feature::query();
    }

    public function getAllByType($active = false, $type = null)
    {
        $query = $this->query()
            ->with([
                'egs',
                'values',
                'values.current',
                'translations',
                'current'
            ]);

        if($active){
            $query->active();
        }

        if($type){
            $query->where('type', $type);
        }

        return $query->orderBy('position')->get();
    }

    public function activeFeature($id): bool
    {
        return Feature::query()
            ->where('id', $id)
            ->where('active', true)
            ->exists();
    }

}

