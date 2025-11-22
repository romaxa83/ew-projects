<?php

namespace App\Services\Filters;

use App\Models\Catalog\Features\Feature;
use App\Models\Catalog\Features\Value;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductFeatureValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

class FilterService
{

    public function getFiltersForSearch(array $args): Collection
    {
        $valuesId = Product::query()
            ->select('value_id')
            ->filter($args)
            ->join(
                ProductFeatureValue::TABLE,
                fn(JoinClause $j) => $j
                    ->on(
                        Product::TABLE . '.id',
                        '=',
                        ProductFeatureValue::TABLE . '.product_id'
                    )
            )
            ->getQuery();

        $values = Value::query()
            ->whereIn(Value::TABLE . '.id', $valuesId)
            ->whereHas(
                'feature',
                fn(Builder|Feature $b) => $b
                    ->where('display_in_filter', true)
            )
            ->addFeatureName()
            ->addSelect(Value::TABLE . '.*', Value::TABLE . '.title as name')
            ->getQuery()
            ->latest(Feature::TABLE . '.sort')
            ->get();

        return $this->mapValuesToGroups($values);
    }

    protected function mapValuesToGroups(Collection $values): Collection
    {
        $groups = [];

        foreach ($values as $value) {
            $group = $groups[$value->feature_id] ?? [];
            $group['feature_name'] = $value->feature_name;
            $group['feature_short_name'] = $value->feature_short_name ?? $value->feature_name;
            $group['values'][] = $value;

            $groups[$value->feature_id] = $group;
        }

        return collect($groups);
    }
}
