<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Str;

class CoveCaseCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?string
    {
        return !is_null($value) ? Str::upper($value) : null;
    }

    public function set($model, string $key, $value, array $attributes): ?string
    {
        return !is_null($value) ? Str::lower($value) : null;
    }
}
