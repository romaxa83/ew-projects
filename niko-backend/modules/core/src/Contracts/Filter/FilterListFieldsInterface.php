<?php

namespace WezomCms\Core\Contracts\Filter;

interface FilterListFieldsInterface
{
    // Column size with control buttons
    public const CONTROL_SIZE = 2;

    /**
     * Generate array with filter fields.
     * @return iterable|FilterFieldInterface[]
     */
    public function getFields(): iterable;
}
