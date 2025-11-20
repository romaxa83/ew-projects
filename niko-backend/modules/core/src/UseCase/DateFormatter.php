<?php

namespace WezomCms\Core\UseCase;

use Carbon\Carbon;

class DateFormatter
{
    public static function convertTimeForFront(string $time): int
    {
        return self::convertTimeForOclock($time) * 1000;
    }

    public static function convertTimestampForFront($timestamp): int
    {
        return $timestamp * 1000;
    }

    public static function convertDateForFront($date)
    {
        if($date){
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $date, 'UTC');
            $date->setTimezone('Europe/Kiev');

            return $date->timestamp * 1000;
        }

        return null;
    }

    public static function convertTimeForOclock(string $time): int
    {
        $today = Carbon::today()->timestamp;
        $dateFromTime = Carbon::createFromTimeString($time)->timestamp;

        return $dateFromTime - $today;
    }

    public static function convertTimestampForBack(string $timestamp)
    {
        $time = $timestamp / 1000;

        return Carbon::createFromTimestampUTC($time);
    }

    public static function convertFor1c(string $timestamp)
    {
        if($timestamp){
            return (int)($timestamp / 1000);
        }
        return null;
    }

    public static function convertDateToTimestamp(string $date)
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $date)->timestamp;
    }

    public static function convertDateToTimestampFor1s(string $date)
    {
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $date, 'UTC');
        $date->setTimezone('Europe/Kiev');

        return $date->timestamp;
    }
}
