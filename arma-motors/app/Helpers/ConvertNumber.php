<?php

namespace App\Helpers;

class ConvertNumber
{
    public static function fromFloatToNumber(string|float $float, $int = 10): int
    {
        return (int)((float)$float * $int);
    }

    public static function fromNumberToFloat(string|int $integer, $int = 10): float
    {
        return (float)((int)$integer / $int);
    }
}
