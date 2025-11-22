<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class PriceCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?float
    {
        return is_null($value) ? null : round(((int)$value)/100, 2);
    }

    public function set($model, string $key, $value, array $attributes): ?int
    {
        if (!is_numeric($value)) {
            return null;
        }

        $value = (float)$value;

        return (int)($value*100);
    }
}
