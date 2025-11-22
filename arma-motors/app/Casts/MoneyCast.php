<?php

namespace App\Casts;

use App\ValueObjects\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class MoneyCast implements CastsAttributes
{

    public function get($model, string $key, $value, array $attributes): ?Money
    {
        if (is_null($value)) {
            return null;
        }

        return Money::instanceFromDbConvert($value);
    }

    public function set($model, string $key, $value, array $attributes): null|int
    {
        if(is_null($value)){
            return  null;
        }

        if (!is_null($value) && !$value instanceof Money) {
            throw new InvalidArgumentException('The given value is not an Money instance.');
        }

//        return Money::instanceToDbConvert($value->getValue())->getValue();
        return (new Money($value->getValue(), true))->getValue();
    }
}

