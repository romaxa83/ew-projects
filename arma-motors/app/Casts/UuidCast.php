<?php

namespace App\Casts;

use App\ValueObjects\Uuid;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class UuidCast implements CastsAttributes
{

    public function get($model, string $key, $value, array $attributes): null|Uuid
    {
        if (is_null($value) || "" == $value) {
            return null;
        }

        return new Uuid($value);
    }

    public function set($model, string $key, $value, array $attributes)
    {
//        dd($value);
//        if (!is_null($value)
////            && !$value instanceof Uuid
//        ) {
//            throw new InvalidArgumentException('The given value is not an Uuid instance.');
//        }

        return $value;
    }
}
