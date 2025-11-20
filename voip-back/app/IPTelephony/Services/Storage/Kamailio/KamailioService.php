<?php

namespace App\IPTelephony\Services\Storage\Kamailio;

use App\Helpers\DbConnections;
use App\IPTelephony\Services\Storage\AbstractServiceStorage;

abstract class KamailioService extends AbstractServiceStorage
{
    public function getDb(): string
    {
        return DbConnections::KAMAILIO;
    }

    abstract function getTable(): string;
}
