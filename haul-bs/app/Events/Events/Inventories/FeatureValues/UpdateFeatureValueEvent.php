<?php

namespace App\Events\Events\Inventories\FeatureValues;

use App\Models\Inventories\Features\Value;

class UpdateFeatureValueEvent
{
    public function __construct(
        protected Value $model
    )
    {}

    public function getModel(): Value
    {
        return $this->model;
    }
}
