<?php
/** @noinspection PhpIncompatibleReturnTypeInspection */

namespace App\Http\Controllers\Api\Helpers;


use Illuminate\Support\Carbon;

class DateTimeHelper
{
    public const DATE_FORMAT = 'm/d/Y';
    public const TIME_FORMAT = 'g:i A';
    public const DATE_TIME_FORMAT = 'm/d/Y g:i A';

    public static function fromDate(string $date, ?string $timezone = null): Carbon
    {
        return Carbon::createFromFormat(self::DATE_FORMAT, $date, $timezone);
    }

    public static function fromTime(string $time, ?string $timezone = null): Carbon
    {
        return Carbon::createFromFormat(self::TIME_FORMAT, $time, $timezone);
    }

    public static function fromDateTime(string $dateTime, ?string $timezone = null): Carbon
    {
        return Carbon::createFromFormat(self::DATE_TIME_FORMAT, $dateTime, $timezone);
    }

    public static function toDate(Carbon $carbon): string
    {
        return $carbon->format(self::DATE_FORMAT);
    }

    public static function toTime(Carbon $carbon): string
    {
        return $carbon->format(self::TIME_FORMAT);
    }

    public static function toDateTime(Carbon $carbon): string
    {
        return $carbon->format(self::DATE_TIME_FORMAT);
    }
}
