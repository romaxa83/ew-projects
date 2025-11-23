<?php

namespace WezomCms\Catalog\Filter\Contracts;

interface StorageInterface
{
    /**
     * @param  bool  $fullSelection
     * @return mixed
     */
    public function beginSelection(bool $fullSelection = true);

    /**
     * @return mixed
     */
    public function beginCount();
}
