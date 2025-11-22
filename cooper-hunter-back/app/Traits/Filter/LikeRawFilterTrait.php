<?php

namespace App\Traits\Filter;

use App\Filters\BaseModelFilter;
use Illuminate\Database\Eloquent\Builder;

trait LikeRawFilterTrait
{
    public function likeRaw(string $field, string $value, ?Builder $builder = null): Builder|BaseModelFilter
    {
        $value = '%' . mb_convert_case($value, MB_CASE_LOWER) . '%';

        $builder = $builder ?? $this;

        return $builder->whereRaw(
            "lower(" . $field . ") LIKE ?",
            [$value]
        );
    }

    public function wrapLikeRaw(string $field, string $value): string
    {
        return 'lower(' . $field . ') like \'%' . $value . '%\'';
    }
}
