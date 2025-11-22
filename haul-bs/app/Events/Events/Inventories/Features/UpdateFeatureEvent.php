<?php

namespace App\Events\Events\Inventories\Features;

use App\Models\Inventories\Features\Feature;

class UpdateFeatureEvent
{
    public function __construct(
        protected Feature $model
    )
    {}

    public function getModel(): Feature
    {
        return $this->model;
    }
}
