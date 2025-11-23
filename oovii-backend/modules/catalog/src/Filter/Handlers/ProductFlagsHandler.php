<?php

namespace WezomCms\Catalog\Filter\Handlers;

use Illuminate\Support\Collection;
use WezomCms\Catalog\Filter\Contracts\FilterFormBuilder;
use WezomCms\Catalog\Filter\Contracts\FilterInterface;
use WezomCms\Catalog\Filter\Contracts\SelectedAttributesInterface;
use WezomCms\Catalog\Filter\Exceptions\IncorrectUrlParameterException;
use WezomCms\Catalog\Filter\SelectionHandlers\KeywordSearch;

class ProductFlagsHandler extends AbstractHandler implements SelectedAttributesInterface
{
    public const NOVELTY = 'novelty';
    public const POPULAR = 'popular';
    public const SALE = 'sale';

    /**
     * Return array of all supported keys.
     *
     * @return array
     */
    public function supportedParameters(): array
    {
        return [static::NOVELTY, static::POPULAR, static::SALE];
    }

    /**
     * @return bool
     * @throws IncorrectUrlParameterException
     */
    public function validateParameters(): bool
    {
        $urlBuilder = $this->filter->getUrlBuilder();
        // Novelty
        if ($urlBuilder->has(static::NOVELTY)) {
            $value = (int) $urlBuilder->first(static::NOVELTY);
            if ($value !== 1) {
                throw new IncorrectUrlParameterException();
            }
        }

        // Popular
        if ($urlBuilder->has(static::POPULAR)) {
            $value = (int) $urlBuilder->first(static::POPULAR);
            if ($value !== 1) {
                throw new IncorrectUrlParameterException();
            }
        }

        //Sale
        if ($urlBuilder->has(static::SALE)) {
            $value = (int) $urlBuilder->first(static::SALE);
            if ($value !== 1) {
                throw new IncorrectUrlParameterException();
            }
        }

        return true;
    }

    /**
     * @param $queryBuilder
     * @param  FilterInterface  $filter
     * @param  array  $criteria
     */
    public function filter($queryBuilder, FilterInterface $filter, array $criteria = [])
    {
        $urlBuilder = $filter->getUrlBuilder();
        if ($urlBuilder->has(static::NOVELTY) || isset($criteria[static::NOVELTY])) {
            $queryBuilder->where('novelty', true);
        }
        if ($urlBuilder->has(static::POPULAR) || isset($criteria[static::POPULAR])) {
            $queryBuilder->where('popular', true);
        }
        if ($urlBuilder->has(static::SALE) || isset($criteria[static::SALE])) {
            $queryBuilder->where('sale', true);
        }
    }

    /**
     * @param  FilterInterface  $filter
     * @return iterable
     */
    public function buildFormData(FilterInterface $filter): iterable
    {
        $urlBuilder = $filter->getUrlBuilder();
        $options = collect();

        /** @var Collection $rows */
        $rows = tap(
            $this->filter->getStorage()
                ->beginSelection(false)
                ->select('products.novelty', 'products.popular', 'products.sale'),
            function ($query) {
                $this->filter->applyOnlyHandlers(
                    $query,
                    [
                        KeywordSearch::class,
                        CategoryHandler::class,
                        CategoryWithSubCategoriesHandler::class
                    ]
                );
            }
        )->toBase()->get();

        if ($rows->contains('novelty', 1)) {
            $selected = $urlBuilder->present(static::NOVELTY, 1);
            $options->push([
                'name' => __('cms-catalog::site.filter.Novelty'),
                'input_name' => static::NOVELTY,
                'value' => 1,
                'disabled' => !$selected && !$filter->hasResultByCriteria([static::NOVELTY => 1]),
                'selected' => $selected,
                'url' => $urlBuilder->autoBuild(static::NOVELTY, 1),
            ]);
        }

        if ($rows->contains('popular', 1)) {
            $selected = $urlBuilder->present(static::POPULAR, 1);
            $options->push([
                'name' => __('cms-catalog::site.filter.Popular'),
                'input_name' => static::POPULAR,
                'value' => 1,
                'disabled' => !$selected && !$filter->hasResultByCriteria([static::POPULAR => 1]),
                'selected' => $selected,
                'url' => $urlBuilder->autoBuild(static::POPULAR, 1),
            ]);
        }

        if ($rows->contains('sale', 1)) {
            $selected = $urlBuilder->present(static::SALE, 1);
            $options->push([
                'name' => __('cms-catalog::site.filter.Sale'),
                'input_name' => static::SALE,
                'value' => 1,
                'disabled' => !$selected && !$filter->hasResultByCriteria([static::SALE => 1]),
                'selected' => $selected,
                'url' => $urlBuilder->autoBuild(static::SALE, 1),
            ]);
        }

        if ($options->isEmpty()) {
            return [];
        }

        /**
         * @var $disabled Collection
         * @var $enabled Collection
         */
        list($disabled, $enabled) = $options->partition('disabled');

        return [
            [
                'type' => FilterFormBuilder::TYPE_CHECKBOX,
                'sort' => 15,
                'title' => __('cms-catalog::site.filter.Tags'),
                'options' => $enabled->merge($disabled)->values()->all(),
            ],
        ];
    }

    /**
     * Generate array with all selected values.
     *
     * @return array
     */
    public function selectedAttributes(): iterable
    {
        $result = [];

        $urlBuilder = $this->filter->getUrlBuilder();
        if ($urlBuilder->has(static::NOVELTY) && $urlBuilder->get(static::NOVELTY)) {
            $result[] = [
                'group' => static::NOVELTY,
                'name' => __('cms-catalog::site.filter.Novelty'),
                'removeUrl' => $urlBuilder->buildUrlWithout(static::NOVELTY, 1),
            ];
        }

        if ($urlBuilder->has(static::POPULAR) && $urlBuilder->get(static::POPULAR)) {
            $result[] = [
                'group' => static::POPULAR,
                'name' => __('cms-catalog::site.filter.Popular'),
                'removeUrl' => $urlBuilder->buildUrlWithout(static::POPULAR, 1),
            ];
        }

        if ($urlBuilder->has(static::SALE) && $urlBuilder->get(static::SALE)) {
            $result[] = [
                'group' => static::SALE,
                'name' => __('cms-catalog::site.filter.Sale'),
                'removeUrl' => $urlBuilder->buildUrlWithout(static::SALE, 1),
            ];
        }

        return $result;
    }
}
