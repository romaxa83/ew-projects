<?php

namespace App\Casts;

use App\ValueObjects\CarNumber;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class CarNumberCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?CarNumber
    {
        if (is_null($value)) {
            return null;
        }

        return new CarNumber($value);
    }

    public function set($model, string $key, $value, array $attributes): string
    {
        if (!is_null($value) && !$value instanceof CarNumber) {
            throw new InvalidArgumentException('The given value is not an CarNumber instance.');
        }

        return (string)$value;
    }
}

