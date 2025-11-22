<?php

namespace App\Helpers;

use App\Exceptions\ErrorsCode;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class DateTime
{
    // 3240000
    // 3240000
    public static function fromSecondsToMilliseconds(string|int $seconds): int
    {
        self::assetData($seconds);

        return (int)$seconds * 1000;
    }

    public static function fromMillisecondToSeconds(string|int $milliseconds): int
    {
        self::assetData($milliseconds);

        if((int)$milliseconds > 0){
            return (int)$milliseconds / 1000;
        }

        return $milliseconds;
    }

    public static function fromMillisecondToDate(string|int $milliseconds, string $format = 'Y-m-d H:i:s')
    {
        self::assetData($milliseconds);

        return Carbon::createFromTimestamp(
            self::fromMillisecondToSeconds($milliseconds)
        )
            ->format($format);
    }

    public static function fromMillisecondToCarboneDate(string|int $milliseconds)
    {
        self::assetData($milliseconds);

        return Carbon::createFromTimestamp(
            self::fromMillisecondToSeconds($milliseconds)
        );
    }

    public static function fromMillisecondToCarboneImutableDate(string|int $milliseconds)
    {
        self::assetData($milliseconds);

        return CarbonImmutable::createFromTimestamp(
            self::fromMillisecondToSeconds($milliseconds)
        );
    }

    public static function fromMillisecondToDayOfWeek(string|int $milliseconds)
    {
        self::assetData($milliseconds);

        return Carbon::createFromTimestamp(
            self::fromMillisecondToSeconds($milliseconds)
        )->dayOfWeek;
    }

    public static function timeFromMillisecond(string|int $milliseconds)
    {
        self::assetData($milliseconds);

        $sec = self::fromMillisecondToSeconds($milliseconds);

        return  round($sec/60/60, 2);
    }

    public static function fromDateToMillisecond(Carbon|CarbonImmutable $date)
    {
        return (string)self::fromSecondsToMilliseconds($date->timestamp);
    }

    public static function assetData($data)
    {
        if(!is_numeric($data)){
            throw new \InvalidArgumentException(__('incorrect data for convert date', ['data' => $data]), ErrorsCode::BAD_REQUEST);
        }
    }
}
