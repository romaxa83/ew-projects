<?php

namespace Wezom\Core\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Wezom\Core\ValueObjects\Phone;

class PhoneCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Phone
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof Phone) {
            return $value;
        }

        return new Phone($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof Phone) {
            return $value->getValue();
        }

        throw new InvalidArgumentException(__('core::exceptions.casts.phone'));
    }
}
