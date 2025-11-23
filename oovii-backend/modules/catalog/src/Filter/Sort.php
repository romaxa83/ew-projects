<?php

namespace WezomCms\Catalog\Filter;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use WezomCms\Catalog\Filter\Contracts\FilterInterface;
use WezomCms\Catalog\Filter\Contracts\ResultFilteringInterface;
use WezomCms\Catalog\Filter\Contracts\SortInterface;
use WezomCms\Catalog\Filter\Exceptions\IncorrectUrlParameterException;

class Sort implements ResultFilteringInterface, SortInterface
{
    /**
     * @var string
     */
    private $urlKey;

    /**
     * @var string
     */
    private $sort;

    /**
     * @var array
     */
    private $variants;

    /**
     * @var bool
     */
    private $urlHasSortField = false;

    /**
     * HandlerInterface constructor.
     * @param  Request  $request
     * @throws IncorrectUrlParameterException
     */
    public function __construct(Request $request)
    {
        $this->urlKey = config('cms.catalog.products.sort.url_key', 'sort');
        $this->variants = config('cms.catalog.products.sort.variants', []);

        foreach (event('catalog.sort_variants') as $item) {
            $this->variants = array_merge($this->variants, (array)$item);
        }

        if ($request->has($this->urlKey)) {
            $sort = $request->get($this->urlKey);

            if (!array_key_exists($sort, $this->variants)) {
                throw new IncorrectUrlParameterException("Sort {$sort} not allowed");
            }

            $this->sort = $sort;
            $this->urlHasSortField = true;
        } else {
            // Set default
            reset($this->variants);
            $this->sort = key($this->variants);
        }
    }

    /**
     * Return all available sorting variants.
     *
     * @return iterable
     */
    public function getAllSortVariants(): iterable
    {
        $result = [];
        foreach ($this->variants as $key => $variant) {
            $result[$key] = __($variant['name']);
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getUrlKey(): string
    {
        return $this->urlKey;
    }

    /**
     * Return current sort model.
     *
     * @return mixed
     */
    public function getCurrentSort()
    {
        return $this->sort;
    }

    /**
     * @param $sort
     * @return bool
     */
    public function isThisSort($sort): bool
    {
        return $sort === $this->sort;
    }

    /**
     * @param  Builder|mixed  $queryBuilder
     * @param  FilterInterface  $filter
     * @param  array  $criteria
     */
    public function filter($queryBuilder, FilterInterface $filter, array $criteria = [])
    {
        $sort = $this->variants[$this->sort];

        if (is_array($sort['field'])) {
            foreach ($sort['field'] as $field => $direction) {
                $queryBuilder->orderBy('products.' . $field, $direction);
            }
        } else {
            $queryBuilder->orderBy('products.' . $sort['field'], array_get($sort, 'direction'));
        }
    }

    /**
     * Check if url has sort key - value.
     *
     * @return bool
     */
    public function urlHasSortKey(): bool
    {
        return $this->urlHasSortField;
    }

    /**
     * Return current sort name.
     *
     * @return mixed
     */
    public function getCurrentSortName()
    {
        return __($this->variants[$this->sort]['name']);
    }
}
