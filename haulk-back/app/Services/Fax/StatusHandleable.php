<?php

namespace App\Services\Fax;

use App\Services\Fax\Handlers\StatusHandler;

interface StatusHandleable
{
    public function getStatusHandler(): StatusHandler;
}
