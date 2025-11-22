<?php

namespace App\Foundations\Helpers;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

/**
 * @property-read ConnectionInterface default()
 * @property-read ConnectionInterface haulk()
 */

class DbConnections
{
    public const DEFAULT = 'pgsql';
    public const HAULK = 'haulk';

    public static function default(): ConnectionInterface
    {
        return static::getConnection(self::DEFAULT);
    }

    public static function haulk(): ConnectionInterface
    {
        return static::getConnection(self::HAULK);
    }

    public static function getConnection(string $connection): ConnectionInterface
    {
        return DB::connection($connection);
    }
}
