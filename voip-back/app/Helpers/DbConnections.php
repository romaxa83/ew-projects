<?php

namespace App\Helpers;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

/**
 * @property-read ConnectionInterface default()
 * @property-read ConnectionInterface kamailio()
 * @property-read ConnectionInterface asterisk()
 */

class DbConnections
{
    public const DEFAULT = 'mysql';
    public const KAMAILIO = 'kamailio';
    public const ASTERISK = 'asterisk';

    public static function default(): ConnectionInterface
    {
        return static::getConnection(self::DEFAULT);
    }

    public static function kamailio(): ConnectionInterface
    {
        return static::getConnection(self::KAMAILIO);
    }

    public static function asterisk(): ConnectionInterface
    {
        return static::getConnection(self::ASTERISK);
    }

    public static function getConnection(string $connection): ConnectionInterface
    {
        return DB::connection($connection);
    }
}
