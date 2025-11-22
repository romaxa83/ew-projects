<?php

namespace App\Casts;

use App\Types\Order\Status;
use App\ValueObjects\Uuid;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class StatusCast implements CastsAttributes
{

    public function get($model, string $key, $value, array $attributes): null|Status
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

