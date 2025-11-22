<?php

namespace App\Traits\Model;

use App\Models\BaseModel;

trait SetSortAfterCreate
{
    public static function boot(): void
    {
        parent::boot();

        static::created(
            static function (BaseModel $model) {
                if (in_array('sort', $model->getFillable())) {
                    $lastValue = static::query()
                        ->select('sort')
                        ->latest('sort')
                        ->where('id', '!=', $model->id)
                        ->getQuery()
                        ->first();
                    $model->sort = $lastValue ? $lastValue->sort + 1 : 1;
                    $model->save();
                }
            }
        );
    }
}
