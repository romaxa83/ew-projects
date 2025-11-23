<?php

namespace WezomCms\Catalog\Filter\Contracts;

use WezomCms\Catalog\Filter\Exceptions\IncorrectSortException;
use WezomCms\Catalog\Filter\Exceptions\IncorrectUrlParameterException;

interface HandlerInterface extends ResultFilteringInterface, FilterFormBuilder
{
    /**
     * HandlerInterface constructor.
     * @param  FilterInterface  $filter
     */
    public function __construct(FilterInterface $filter);

    /**
     * Return array of all supported keys.
     *
     * @return array
     */
    public function supportedParameters(): array;

    /**
     * @return bool
     * @throws IncorrectUrlParameterException
     * @throws IncorrectSortException
     */
    public function validateParameters(): bool;
}
