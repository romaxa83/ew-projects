<?php

namespace WezomCms\Catalog\Filter;

use WezomCms\Catalog\Models\Category;

trait AttachedCategory
{
    /**
     * @var mixed|Category
     */
    protected $category = null;

    /**
     * @param $category
     * @return mixed
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }
}
