<?php

namespace App\Events\Events\Inventories\Brands;

use App\Models\Inventories\Brand;

class UpdateBrandEvent
{
    public function __construct(
        protected Brand $model
    )
    {}

    public function getModel(): Brand
    {
        return $this->model;
    }
}
