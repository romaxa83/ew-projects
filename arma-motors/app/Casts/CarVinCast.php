<?php

namespace App\Casts;

use App\ValueObjects\CarVin;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class CarVinCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?CarVin
    {
        if (is_null($value)) {
            return null;
        }

        return new CarVin($value);
    }

    public function set($model, string $key, $value, array $attributes): string
    {
        if (!is_null($value) && !$value instanceof CarVin) {
            throw new InvalidArgumentException('The given value is not an CarVin instance.');
        }

        return (string)$value;
    }
}
