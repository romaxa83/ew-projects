<?php

namespace App\Traits\Model;

use App\Models\BaseModel;

trait NormalizeId
{
    protected function normalizeId(BaseModel|int $model): int
    {
        if ($model instanceof BaseModel) {
            $model = $model->id;
        }

        return $model;
    }
}
