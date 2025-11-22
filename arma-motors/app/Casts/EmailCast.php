<?php

namespace App\Casts;

use App\ValueObjects\Email;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class EmailCast implements CastsAttributes
{

    public function get($model, string $key, $value, array $attributes): ?Email
    {
        return null != $attributes['email'] ? new Email($attributes['email']) : null;
    }

    public function set($model, string $key, $value, array $attributes): null|string
    {
        if (null === $value) {
            return null;
        }

        if (is_string($value)) {
            $value = new Email($value);
        }

        if (!$value instanceof Email) {
            throw new InvalidArgumentException('The given value is not an Email instance.');
        }

        return (string)$value;
    }
}
