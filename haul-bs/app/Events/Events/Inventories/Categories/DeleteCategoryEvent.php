<?php

namespace App\Events\Events\Inventories\Categories;

use App\Models\Inventories\Category;

class DeleteCategoryEvent
{
    public function __construct(
        protected Category $model
    )
    {}

    public function getModel(): Category
    {
        return $this->model;
    }
}
