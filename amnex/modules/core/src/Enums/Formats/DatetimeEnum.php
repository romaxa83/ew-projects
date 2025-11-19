<?php

declare(strict_types=1);

namespace Wezom\Core\Enums\Formats;

enum DatetimeEnum: string
{
    case DEFAULT = 'datetime:' . self::DEFAULT_FORMAT->value;
    case DEFAULT_FORMAT = 'Y-m-d H:i:s';
    case DATE = 'Y-m-d';
    case TIME = 'H:i:s';
    case TIME_WITHOUT_SECONDS = 'H:i';
    case CAST_TIME = 'date_format:' . self::TIME->value;
    case AMERICAN_DATETIME_FORMAT = 'm-d-Y H:i:s';
    case AMERICAN_DATETIME_FORMAT_VALIDATION_RULE = 'date_format:' . self::AMERICAN_DATETIME_FORMAT->value;
    case AMERICAN_DATE_FORMAT_VALIDATION_RULE = 'date_format:' . self::AMERICAN_DATE_FORMAT->value;
    case AMERICAN_DATE_FORMAT = 'm-d-Y';
    case AMERICAN_DATE_FORMAT2 = 'm/d/Y';
}
