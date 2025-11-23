<?php

namespace WezomCms\Catalog\Filter\Contracts;

use Illuminate\Http\Request;

interface SortInterface
{
    /**
     * Sort constructor.
     * @param  Request  $request
     * @throws \InvalidArgumentException
     */
    public function __construct(Request $request);

    /**
     * Return all available sorting variants.
     *
     * @return iterable
     */
    public function getAllSortVariants(): iterable;

    /**
     * @return string
     */
    public function getUrlKey(): string;

    /**
     * Return current sort model.
     *
     * @return mixed
     */
    public function getCurrentSort();

    /**
     * @param $sort
     * @return bool
     */
    public function isThisSort($sort): bool;

    /**
     * Check if url has sort key - value.
     *
     * @return bool
     */
    public function urlHasSortKey(): bool;

    /**
     * Return current sort name.
     *
     * @return mixed
     */
    public function getCurrentSortName();
}
