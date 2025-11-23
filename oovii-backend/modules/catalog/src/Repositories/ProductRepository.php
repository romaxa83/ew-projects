<?php

namespace WezomCms\Catalog\Repositories;

use Closure;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Repositories\AbstractRepository;

class ProductRepository extends AbstractRepository
{
    protected function query(): Builder
    {
        return Product::query();
    }

    public function getBySelectWithName(): array
    {
        return $this->query()
            ->published()
            ->with([
                'translations' => function ($query) {
                    $query->orderBy('name');
                }
            ])
            ->orderBy('sort')
            ->get()
            ->pluck('name', 'id')
            ->toArray()
        ;
    }

    public function getGroup(bool $trashed = false): Collection
    {
        return $this->query()
                ->select(['group_key', DB::raw('count(*) as total')])
                ->where('group_key', '!=', null)
                ->when(
                    $trashed,
                    fn (Builder $query) => $query->onlyTrashed()
                )
                ->groupBy('group_key')
                ->get();
    }

    public function groupKeyForFilter(bool $trashed = false): array
    {
        $temp = [];
        foreach ($this->getGroup($trashed) as $item){
            $temp[$item->group_key] = " {$item->group_key} ($item->total)";
        }
        return $temp;
    }

    public function getProductsByGroupKey($groupKey, $relations = [], $withoutID = null): EloquentCollection
    {
        $q = $this->query()
            ->with($relations)
            ->where('group_key', $groupKey);

        if ($withoutID) {
            $q->where('id', '!=', $withoutID);
        }

        return $q->get();
    }

    public function getMaxPrice($filter = [])
    {
        return $this->query()
            ->filter($filter)
            ->max(DB::raw('CASE WHEN cost_discount = 0 THEN cost ELSE cost_discount END'));
    }

    public function getMinPrice($filter = [])
    {
        return $this->query()
            ->filter($filter)
            ->min(DB::raw('CASE WHEN cost_discount = 0 THEN cost ELSE cost_discount END'));
    }

    public function existByImport(string $name, array $specifications = []): bool
    {
        $q = $this->query()
            ->whereHas("translations", function($q) use ($name) {
                $q->where("name", $name);
            });

        foreach ($specifications ?? [] as $item){
            $q->whereHas("productSpecifications", function($q) use ($item) {
                $q->where([
                    ['spec_id', $item['spec_id']],
                    ['spec_value_id', $item['value_id']]
                ]);
            });
        }

        return $q->exists();
    }

    public function getAll(
        array $relation = [],
        $withActive = false,
        array $filter = [],
        $published = true,
        array $order = []
    ) {
        $query = $this->query()
            ->filter($filter)
            ->with($relation);

        if ($published) {
            $query->published();
        }
        if ($withActive) {
            $query->active();
        }
        if (!empty($order)) {
            foreach ($order as $field => $type) {
                if ($closure = $this->customOrder($field, $type)) {
                    $closure($query);
                } else {
                    $query->orderBy($field, $type);
                }
            }
        }

        return $query->get();
    }

    private function customOrder(string $field, string $type): ?Closure
    {
        return match ($field) {
            'price' => static fn (Builder $query) => $query->orderByRaw(
                'CASE WHEN cost_discount = 0 THEN cost ELSE cost_discount END ' . Str::upper($type)),
            default => null,
        };
    }
}
