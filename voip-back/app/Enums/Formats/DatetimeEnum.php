<?php

namespace App\Enums\Formats;

final class DatetimeEnum
{
    public const DEFAULT = 'datetime:' . self::DEFAULT_FORMAT;

    public const DEFAULT_FORMAT = 'Y-m-d H:i:s';
    public const FOR_EXCEL_FILE = 'm/d/Y, h:i:s a';

    public const DATE = 'Y-m-d';
    public const DATE_SLASH = 'Y/m/d';

    public const TIME = 'H:i:s';
    public const TIME_WITHOUT_SECONDS = 'H:i';

    public const DATE_RULE = 'date_format:' . self::DATE;
    public const DATE_TIME_RULE = 'date_format:' . self::DEFAULT_FORMAT;
    public const CAST_TIME = 'date_format:' . self::TIME;
}
