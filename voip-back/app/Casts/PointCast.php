<?php

namespace App\Casts;

use App\ValueObjects\Point;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Query\Expression;
use InvalidArgumentException;

class PointCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): Point
    {
        if ($value instanceof Point) {
            return $value;
        }

        if (is_string($value)) {
            return Point::decode($value);
        }

        throw new InvalidArgumentException('Invalid Point value given.');
    }

    public function set($model, string $key, $value, array $attributes): Expression
    {
        if (!$value instanceof Point) {
            throw new InvalidArgumentException('Value should be a Point instance.');
        }

        return $value->getGeoPoint();
    }
}
