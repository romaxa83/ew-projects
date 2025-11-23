<?php

namespace WezomCms\Catalog\Filter\Contracts;

interface HasAnAttachedCategoryInterface
{
    /**
     * @param $category
     * @return mixed
     */
    public function setCategory($category);

    /**
     * @return mixed
     */
    public function getCategory();
}
