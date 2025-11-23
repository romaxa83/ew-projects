<?php

namespace WezomCms\Catalog\Filter\Handlers;

use Cache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use WezomCms\Catalog\Filter\Contracts\FilterFormBuilder;
use WezomCms\Catalog\Filter\Contracts\FilterInterface;
use WezomCms\Catalog\Filter\Contracts\SelectedAttributesInterface;
use WezomCms\Catalog\Filter\Contracts\TemplateParametersInterface;
use WezomCms\Catalog\Filter\Exceptions\IncorrectUrlParameterException;
use WezomCms\Catalog\Filter\SelectionHandlers\KeywordSearch;
use WezomCms\Catalog\Filter\Sort;
use WezomCms\Catalog\Models\ProductSpecification;
use WezomCms\Catalog\Models\Specifications\Specification;

class SpecificationHandler extends AbstractHandler implements SelectedAttributesInterface, TemplateParametersInterface
{
    /**
     * @return array
     */
    public function supportedParameters(): array
    {
        return $this->getSpecsWithValues()->pluck('slug')->filter()->toArray();
    }

    /**
     * @return bool
     * @throws IncorrectUrlParameterException
     */
    public function validateParameters(): bool
    {
        $urlBuilder = $this->filter->getUrlBuilder();

        foreach ($this->getSpecsWithValues() as $spec) {
            if ($urlBuilder->has($spec->slug)) {
                $values = $urlBuilder->get($spec->slug, []);

                if (empty($values)) {
                    throw new IncorrectUrlParameterException();
                }

                $dbCount = $spec->publishedSpecValues()
                    ->whereIn('slug', $values)
                    ->count();

                if (count($values) !== $dbCount) {
                    throw new IncorrectUrlParameterException();
                }
            }
        }

        return true;
    }

    /**
     * @param  Builder  $queryBuilder
     * @param  FilterInterface  $filter
     * @param  array  $criteria
     */
    public function filter($queryBuilder, FilterInterface $filter, array $criteria = [])
    {
        $selected = [];
        foreach ($this->selected() as $spec) {
            $selected[$spec->id] = $spec->publishedSpecValues->pluck('id')->toArray();
        }

        if (!empty($criteria)) {
            foreach ($criteria as $criterion) {
                if (isset($selected[$criterion->specification_id])) {
                    $selected[$criterion->specification_id][] = $criterion->id;
                } else {
                    $selected[$criterion->specification_id] = [$criterion->id];
                }
            }
        }

        $count = count($selected);
        if ($count) {
            $queryBuilder->join('product_specifications', 'product_specifications.product_id', '=', 'products.id');

            $queryBuilder->where(function ($query) use ($selected) {
                foreach ($selected as $specId => $specValues) {
                    $query->orWhere(function ($query) use ($specId, $specValues) {
                        $query->where('spec_id', $specId)
                            ->whereIn('spec_value_id', $specValues);
                    });
                }
            });

            $queryBuilder->having(DB::raw('COUNT(DISTINCT product_specifications.spec_id)'), '>=', $count);
        }

        // Apply grouping for select products. Not for aggregation.
        if (!array_get($queryBuilder->getQuery()->columns, 0) instanceof Expression) {
            if ($count) {
                $queryBuilder->groupBy('products.id');
            }

            $queryBuilder->groupBy(DB::raw('COALESCE(`products`.`group_key`, `products`.`id`)'));
        }
    }

    /**
     * @param  FilterInterface  $filter
     * @return iterable
     */
    public function buildFormData(FilterInterface $filter): iterable
    {
        $result = [];

        $urlBuilder = $this->filter->getUrlBuilder();

        foreach ($this->getValuesWithDisabledFlag() as $spec) {
            $options = collect();
            $specSlug = $spec->slug;

            foreach ($spec->publishedSpecValues as $specValue) {
                $selected = $urlBuilder->present($specSlug, $specValue->slug);
                $options->push([
                    'obj' => $specValue,
                    'id' => $specValue->id,
                    'name' => $specValue->name,
                    'value' => $specValue->slug,
                    'selected' => $selected,
                    'disabled' => $selected ? false : $specValue->disabled,
                    'url' => $urlBuilder->autoBuild($specSlug, $specValue->slug),
                ]);
            }

            /**
             * @var $disabled Collection
             * @var $enabled Collection
             */
            list($disabled, $enabled) = $options->partition('disabled');

            $result[] = [
                'type' => FilterFormBuilder::TYPE_CHECKBOX,
                'name' => $specSlug,
                'sort' => $spec->sort + 100,
                'title' => $spec->name,
                'options' => $enabled->merge($disabled)->values()->all(),
            ];
        }

        return $result;
    }

    /**
     * Generate array with all selected values.
     *
     * @return array
     */
    public function selectedAttributes(): iterable
    {
        $result = [];

        $selected = $this->selected()->sortBy('sort')->sortByDesc('id');
        if ($selected->isNotEmpty()) {
            $urlBuilder = $this->filter->getUrlBuilder();

            foreach ($selected as $spec) {
                $values = $spec->publishedSpecValues->sortBy('sort')->sortByDesc('id');
                foreach ($values as $specValue) {
                    $result[] = [
                        'group' => $spec->id,
                        'name' => $specValue->name,
                        'removeUrl' => $urlBuilder->buildUrlWithout($spec->slug, $specValue->slug),
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * @return array|Collection|mixed
     */
    protected function selected()
    {
        return Cache::driver('array')->rememberForever(__METHOD__, function () {
            $urlBuilder = $this->filter->getUrlBuilder();

            $specs = $urlBuilder->getParameters($this->supportedParameters());
            $specs = array_filter((array) $specs);

            if (!$specs) {
                return collect();
            }

            return Specification::published()
                ->whereIn('slug', array_keys($specs))
                ->with([
                    'publishedSpecValues' => function ($query) use ($specs) {
                        $query->whereIn('slug', array_reduce($specs, 'array_merge', []));
                    }
                ])
                ->get();
        });
    }

    /**
     * @return array|Collection|mixed
     */
    private function getSpecsWithValues()
    {
        return Cache::driver('array')->rememberForever(__METHOD__, function () {
            return Specification::published()
                ->with([
                    'publishedSpecValues' => function ($query) {
                        $query->sorting();
                    }
                ])
                ->whereHas('publishedSpecValues', function ($query) {
                    $query->whereHas('products', published_scope());
                })
                ->sorting()
                ->get();
        });
    }

    /**
     * Return array of all supported parameters
     *
     * @return array
     */
    public static function availableParameters(): iterable
    {
        return Specification::published()
            ->get()
            ->pluck('name', 'id')
            ->map(function ($item, $key) {
                return "$item - [$key]";
            })
            ->toArray();
    }

    /**
     * @return array|Collection
     */
    private function getValuesWithDisabledFlag()
    {
        /** @var \Illuminate\Database\Query\Builder $subQuery */
        $subQuery = tap($this->filter->getStorage()->beginSelection(false), function ($query) {
            $this->filter->applyHandlers($query, [static::class, Sort::class]);
        })->select('products.id')
            ->distinct();

        $cacheKey = sha1(json_encode([
            'class' => __CLASS__,
            'method' => __METHOD__,
            'query' => $subQuery->toSql(),
            'binding' => $subQuery->getBindings()
        ]));

        $callback = function () use ($subQuery) {
            $relations = [];
            ProductSpecification::toBase()
                ->orderBy('id')
                ->whereIn('product_id', $subQuery)
                ->chunk(100000, function ($items) use (&$relations) {
                    foreach ($items as $rel) {
                        if (!isset($relations[$rel->product_id])) {
                            $relations[$rel->product_id] = [];
                        }
                        if (!isset($relations[$rel->product_id][$rel->spec_id])) {
                            $relations[$rel->product_id][$rel->spec_id] = [];
                        }

                        $relations[$rel->product_id][$rel->spec_id][] = $rel->spec_value_id;
                    }
                });

            return $relations;
        };

        // Cache result
        if (Cache::supportsTags()) {
            $relations = Cache::tags(ProductSpecification::class)->rememberForever($cacheKey, $callback);
        } else {
            $relations = Cache::remember($cacheKey, now()->addHour(), $callback);
        }


        $selected = [];
        foreach ($this->selected() as $spec) {
            $selected[$spec->id] = $spec->publishedSpecValues->pluck('id')->toArray();
        }

        $specValues = $this->removeNotRelatedSpecifications($this->getSpecsWithValues());

        foreach ($specValues as $specsWithValue) {
            foreach ($specsWithValue->publishedSpecValues as $value) {
                // Add current value
                $curSelected = $selected;

                $curSelected[$specsWithValue->id] = [$value->id];

                $disabled = true;

                // All products
                foreach ($relations as $productId => $relation) {
                    $goodProduct = true;

                    // All selected specifications
                    foreach ($curSelected as $specId => $valuesIds) {
                        if (!isset($relation[$specId])) {
                            $goodProduct = false;

                            break;
                        }

                        $present = false;
                        foreach ($valuesIds as $vId) {
                            if (in_array($vId, $relation[$specId])) {
                                $present = true;
                                break;
                            }
                        }

                        if (!$present) {
                            $goodProduct = false;
                            break;
                        }
                    }

                    if ($goodProduct) {
                        $disabled = false;
                        break;
                    }
                }

                $value->disabled = $disabled;
            }
        }

        return $specValues;
    }

    /**
     * @param $specValues
     * @return Collection
     */
    private function removeNotRelatedSpecifications($specValues)
    {
        $subQuery = tap($this->filter->getStorage()->beginSelection(false), function ($query) {
            $this->filter->applyOnlyHandlers(
                $query,
                [
                    KeywordSearch::class,
                    CategoryHandler::class,
                    CategoryWithSubCategoriesHandler::class
                ]
            );
        })->select('products.id')
            ->distinct();

        $allProductSpecRelation = ProductSpecification::toBase()
            ->whereIn('product_id', $subQuery)
            ->get()
            ->groupBy('spec_id')
            ->map(function ($values) {
                return $values->pluck('spec_value_id');
            })
            ->toArray();

        $filteredSpecValues = collect();
        foreach ($specValues as $specVal) {
            if (array_key_exists($specVal->id, $allProductSpecRelation)) {
                $values = collect();
                foreach ($specVal->publishedSpecValues as $v) {
                    if (in_array($v->id, $allProductSpecRelation[$specVal->id])) {
                        $values->push($v);
                    }
                }

                if ($values->isNotEmpty()) {
                    $newSpec = clone $specVal;
                    $newSpec->publishedSpecValues = $values;

                    $filteredSpecValues->push($newSpec);
                }
            }
        }

        return $filteredSpecValues;
    }
}
