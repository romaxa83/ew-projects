<?php

namespace App\Enums\Formats;

final class DatetimeEnum
{
    public const DEFAULT = 'datetime:' . self::DEFAULT_FORMAT;

    public const DEFAULT_FORMAT = 'Y-m-d H:i:s';

    public const DATE = 'Y-m-d';

    public const TIME = 'H:i:s';
    public const TIME_WITHOUT_SECONDS = 'H:i';

    public const CAST_TIME = 'date_format:' . self::TIME;
}
