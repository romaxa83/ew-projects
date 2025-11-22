<?php

namespace App\Traits\Model;

use App\Models\BaseModel;

trait ToggleActive
{
    public function toggleActive(BaseModel $model): BaseModel
    {
        $model->active = !$model->active;
        $model->save();

        return $model;
    }
}
