<?php

namespace App\Helpers;

class Month
{
    public static function month(): array
    {
        return [
            '01' => 'january',
            '02' => 'february',
            '03' => 'march',
            '04' => 'april',
            '05' => 'may',
            '06' => 'june',
            '07' => 'july',
            '08' => 'august',
            '09' => 'september',
            '10' => 'october',
            '11' => 'november',
            '12' => 'december',
        ];
    }

    public static function monthAsArray(): array
    {
        return [
            'january',
            'february',
            'march',
            'april',
            'may',
            'june',
            'july',
            'august',
            'september',
            'october',
            'november',
            'december',
        ];
    }
}

