<?php

namespace App\Helpers;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

class DbConnections
{
    public const DEFAULT = 'mysql';
    public const OLMO    = 'olmo';

    public static function default(): ConnectionInterface
    {
        return static::getConnection(self::DEFAULT);
    }

    public static function olmo(): ConnectionInterface
    {
        return static::getConnection(self::OLMO);
    }

    public static function getConnection(string $connection): ConnectionInterface
    {
        return DB::connection($connection);
    }
}
