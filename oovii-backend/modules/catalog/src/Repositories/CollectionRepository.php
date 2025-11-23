<?php

namespace WezomCms\Catalog\Repositories;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use WezomCms\Catalog\Models\Collections\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use WezomCms\Core\Repositories\AbstractRepository;

class CollectionRepository extends AbstractRepository
{
    protected function query(): Builder
    {
        return Collection::query();
    }

    public function getAllForFront(
        array $relations = [],
        array $filter = [],
        array $order = []
    ): EloquentCollection
    {
        $query = $this->query()
            ->with($relations)
            ->filter($filter)
            ->published()
            //->where('start_at', "<", Carbon::now())
            ->where('end_at', ">", CarbonImmutable::now());

        if(!empty($order)){
            foreach ($order as $field => $type) {
                $query->orderBy($field, $type);
            }
        }

        return $query->get();
    }

    public function forSelect(
        array $relations = [],
        string $orderField = 'id',
        string $field = 'name',
        bool $active = true,
        $choiceText = true,
        string $activeField = 'active'
    ): array
    {
        $query = $this->query();

        $models = $query
            ->with($relations)
            ->orderBy($orderField)
            ->get()
            ->pluck($field, 'id')
            ->toArray();

        return $models;
    }

    public function collectionStart(): EloquentCollection
    {
        $now = CarbonImmutable::now();
        return $this->query()
            ->published()
            ->where('is_send_start', false)
            ->where('start_at', '<', $now)
            ->where('end_at', '>', $now)
            ->get();
    }

    public function collectionFinish(): EloquentCollection
    {
        $now = CarbonImmutable::now();
        return $this->query()
            ->published()
            ->where('end_at', '<', $now)
            ->get();
    }

    public function collectionSoonFinish(int $hour): EloquentCollection
    {
        $to = CarbonImmutable::now()->addHours($hour);
        $from = CarbonImmutable::now();

        return $this->query()
            ->published()
            ->whereBetween('end_at', [$from, $to])
            ->where('is_send_finish', false)
            ->get();
    }
}
