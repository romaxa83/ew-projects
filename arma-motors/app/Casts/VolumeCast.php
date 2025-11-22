<?php

namespace App\Casts;

use App\ValueObjects\Volume;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class VolumeCast implements CastsAttributes
{

    public function get($model, string $key, $value, array $attributes): ?Volume
    {
        if (is_null($value)) {
            return null;
        }

        return Volume::instanceFromDbConvert($value);
    }

    public function set($model, string $key, $value, array $attributes): null|int
    {
        if(is_null($value)){
            return  null;
        }

        if (!is_null($value) && !$value instanceof Volume) {
            throw new InvalidArgumentException('The given value is not an Volume instance.');
        }

        return Volume::instanceToDbConvert($value)->getValue();
    }
}
