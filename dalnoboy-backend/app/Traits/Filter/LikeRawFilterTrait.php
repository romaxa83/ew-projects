<?php

namespace App\Traits\Filter;

use Illuminate\Database\Eloquent\Builder;

trait LikeRawFilterTrait
{
    public function likeRaw(string $field, string $value, ?Builder $builder = null): mixed
    {
        $value = '%' . mb_convert_case($value, MB_CASE_LOWER) . '%';

        $builder = $builder ?? $this;

        $model = $builder->getModel();

        $field = in_array($field, $model->getFillable()) ? $model->getTable() . '.`' . $field . '`' : $field;

        return $builder->whereRaw(
            "lower(" . $field . ") LIKE ?",
            [$value]
        );
    }
}
