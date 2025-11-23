<?php

namespace WezomCms\Catalog\Filter\Contracts;

interface SelectedAttributesInterface
{
    /**
     * Generate array with all selected values.
     *
     * @return array
     */
    public function selectedAttributes(): iterable;
}
