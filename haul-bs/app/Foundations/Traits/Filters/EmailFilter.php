<?php

namespace App\Foundations\Traits\Filters;

use Illuminate\Database\Eloquent\Builder;

trait EmailFilter
{
    public function email(string $value)
    {
        $this->where(
            function (Builder $query) use ($value) {
                return $query->orWhereRaw('lower(email) like ?', ['%' . escape_like(mb_convert_case($value, MB_CASE_LOWER)) . '%']);
            }
        );
    }
}
