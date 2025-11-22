<?php

namespace App\Casts;

use App\ValueObjects\Phone;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class PhoneCast implements CastsAttributes
{

    public function get($model, string $key, $value, array $attributes): ?Phone
    {
        if (is_null($value)) {
            return null;
        }

        return new Phone($value);
    }

    public function set($model, string $key, $value, array $attributes): string
    {
        if (!is_null($value) && !$value instanceof Phone) {
            throw new InvalidArgumentException('The given value is not an Phone instance.');
        }

        return (string)$value;
    }
}
