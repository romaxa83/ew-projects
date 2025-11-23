<?php

namespace App\Helpers;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

/**
 * @property-read ConnectionInterface default()
 * @property-read ConnectionInterface ringostat()
 * @property-read ConnectionInterface audit()
 * @property-read ConnectionInterface mailbox()
 * @property-read ConnectionInterface import()
 */

class DbConnections
{
    public const DEFAULT = 'mysql';
    public const RINGOSTAT = 'mysqlRingostat';
    public const AUDIT = 'mysqlAudit';
    public const MAILBOX = 'mysqlMailboxes';
    public const IMPORT = 'mysqlImport';

    public static function default(): ConnectionInterface
    {
        return static::getConnection(self::DEFAULT);
    }

    public static function ringostat(): ConnectionInterface
    {
        return static::getConnection(self::RINGOSTAT);
    }

    public static function audit(): ConnectionInterface
    {
        return static::getConnection(self::AUDIT);
    }

    public static function mailbox(): ConnectionInterface
    {
        return static::getConnection(self::MAILBOX);
    }

    public static function import(): ConnectionInterface
    {
        return static::getConnection(self::IMPORT);
    }

    public static function getConnection(string $connection): ConnectionInterface
    {
        return DB::connection($connection);
    }
}
