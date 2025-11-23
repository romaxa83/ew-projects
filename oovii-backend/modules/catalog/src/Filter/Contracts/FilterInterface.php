<?php

namespace WezomCms\Catalog\Filter\Contracts;

use Illuminate\Database\Eloquent\Builder;
use WezomCms\Catalog\Filter\Exceptions\NeedRedirectException;

interface FilterInterface
{
    /**
     * FilterInterface constructor.
     * @param  StorageInterface  $storage
     * @param  UrlBuilderInterface  $urlBuilder
     */
    public function __construct(StorageInterface $storage, UrlBuilderInterface $urlBuilder);

    /**
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface;

    /**
     * @param  ResultFilteringInterface  $handler
     * @return FilterInterface
     */
    public function addHandler(ResultFilteringInterface $handler): FilterInterface;

    /**
     * @param  iterable  $handlers
     * @return FilterInterface
     */
    public function addHandlers(iterable $handlers): FilterInterface;

    /**
     * @return mixed
     * @throws NeedRedirectException
     */
    public function start(): FilterInterface;

    /**
     * @param  int|null  $perPage
     * @param  array|string  $exceptHandlers
     * @param  array  $criteria
     * @return mixed
     */
    public function getFilteredItems(?int $perPage = null, $exceptHandlers = [], array $criteria = []);

    /**
     * @param  array|string  $exceptHandlers
     * @param  array  $criteria
     * @return mixed|Builder
     */
    public function filteredItemsQuery($exceptHandlers = [], array $criteria = []);

    /**
     * @return mixed
     */
    public function countFilteredItems();

    /**
     * @return mixed
     */
    public function checkParameters();

    /**
     * @param $criteria
     * @return bool
     */
    public function hasResultByCriteria($criteria): bool;

    /**
     * @param $column
     * @param  array|mixed  $exceptHandlers
     * @param  callable|null  $callback
     * @return mixed
     */
    public function getMinMaxFor($column, $exceptHandlers = [], callable $callback = null);

    /**
     * @return UrlBuilderInterface
     */
    public function getUrlBuilder(): UrlBuilderInterface;

    /**
     * @return iterable
     */
    public function buildWidgetData(): iterable;

    /**
     * @return iterable
     */
    public function getSelectedAttributes(): iterable;

    /**
     * @param $queryBuilder
     * @param  array|mixed  $exceptHandlers
     * @param  array  $criteria
     */
    public function applyHandlers($queryBuilder, $exceptHandlers = [], array $criteria = []);

    /**
     * @param $queryBuilder
     * @param  array  $handlers
     */
    public function applyOnlyHandlers($queryBuilder, $handlers = []);
}
