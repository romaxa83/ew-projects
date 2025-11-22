<?php

namespace App\Http\Controllers\Api\Helpers;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

/**
 * @property-read ConnectionInterface default()
 * @property-read ConnectionInterface backup()
 * @property-read ConnectionInterface gps()
 */

class DbConnections
{
    public const DEFAULT = 'pgsql';
    public const BACKUP = 'pgsql_backup';
    public const GPS = 'pgsql_gps';

    public static function default(): ConnectionInterface
    {
        return static::getConnection(self::DEFAULT);
    }

    public static function backup(): ConnectionInterface
    {
        return static::getConnection(self::BACKUP);
    }

    public static function gps(): ConnectionInterface
    {
        return static::getConnection(self::GPS);
    }

    public static function getConnection(string $connection): ConnectionInterface
    {
        return DB::connection($connection);
    }
}
