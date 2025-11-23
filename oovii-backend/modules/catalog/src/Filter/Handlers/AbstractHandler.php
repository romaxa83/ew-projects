<?php

namespace WezomCms\Catalog\Filter\Handlers;

use WezomCms\Catalog\Filter\Contracts\FilterInterface;
use WezomCms\Catalog\Filter\Contracts\HandlerInterface;

abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @var FilterInterface
     */
    protected $filter;

    /**
     * AbstractHandler constructor.
     * @param  FilterInterface  $filter
     */
    public function __construct(FilterInterface $filter)
    {
        $this->filter = $filter;
    }
}
