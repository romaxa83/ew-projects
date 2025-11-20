<?php

namespace App\IPTelephony\Services\Storage\Asterisk;

use App\Helpers\DbConnections;
use App\IPTelephony\Services\Storage\AbstractServiceStorage;

abstract class AsteriskService extends AbstractServiceStorage
{
    public function getDb(): string
    {
        return DbConnections::ASTERISK;
    }

    abstract function getTable(): string;
}

