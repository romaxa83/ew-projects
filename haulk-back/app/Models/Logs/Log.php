<?php

namespace App\Models\Logs;

use App\ModelFilters\Logs\LogFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use danielme85\LaravelLogToDB\Models\LogToDbCreateObject;

/**
 * @property int id
 * @property string message
 * @property string channel
 * @property int level
 * @property string level_name
 * @property int unix_time
 * @property string datetime
 * @property string context
 * @property string extra
 * @property string created_at
 * @property string updated_at
 *
 */
class Log extends BaseModel
{
    use Filterable;
    use LogToDbCreateObject;

    public const TABLE = 'log';

    public const DEBUG = 100;
    public const INFO = 200;
    public const NOTICE = 250;
    public const WARNING = 300;
    public const ERROR = 400;
    public const CRITICAL = 500;
    public const ALERT = 550;
    public const EMERGENCY = 600;

    public const DEBUG_NAME = 'DEBUG';
    public const INFO_NAME = 'INFO';
    public const WARNING_NAME = 'INFO';
    public const NOTICE_NAME = 'NOTICE';
    public const ERROR_NAME = 'DEBUG';
    public const CRITICAL_NAME = 'DEBUG';
    public const ALERT_NAME = 'ALERT';
    public const EMERGENCY_NAME = 'EMERGENCY';

    protected $table = self::TABLE;

    public static function getLevels(): array
    {
        return [
            self::DEBUG => self::DEBUG_NAME,
            self::INFO => self::DEBUG_NAME,
            self::NOTICE => self::INFO_NAME,
            self::WARNING => self::WARNING_NAME,
            self::ERROR => self::ERROR_NAME,
            self::CRITICAL => self::CRITICAL_NAME,
            self::ALERT => self::ALERT_NAME,
            self::EMERGENCY => self::EMERGENCY_NAME,
        ];
    }

    public function modelFilter(): string
    {
        return LogFilter::class;
    }
}
