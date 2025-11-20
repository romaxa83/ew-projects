<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateFormat
{
    public static function front($date)
    {
        if($date){
            return Carbon::parse($date)->format('d.m.Y H:i');
        }

        return '';
    }

    public static function pdf($date)
    {
        if($date){
            return Carbon::parse($date)->format(DATE_ATOM);
        }

        return '';
    }

    public static function forTitle($date)
    {
        if($date){
            return Carbon::parse($date)->format('d-m-Y');
        }

        return '';
    }

    public static function timestampMsToDate($timestamp): Carbon
    {
        $time = round((int)$timestamp / 1000);

        return Carbon::createFromTimestamp((int)$time);
    }

    public static function dateToTimestampMs(Carbon $date): int
    {
        return (int)$date->getPreciseTimestamp(3);
    }
}
