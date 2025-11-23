<?php

namespace WezomCms\Catalog\Filter\Contracts;

/**
 * Interface FilteringInterface
 */
interface ResultFilteringInterface
{
    /**
     * @param $queryBuilder
     * @param  FilterInterface  $filter
     * @param  array  $criteria
     */
    public function filter($queryBuilder, FilterInterface $filter, array $criteria = []);
}
