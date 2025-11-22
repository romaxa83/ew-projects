<?php

namespace App\GraphQL\Subscriptions\BackOffice;

use App\WebSocket\Broadcasts\BackOfficeWsBroadcaster;
use Core\WebSocket\Broadcasts\BaseWsBroadcaster;

trait BackOfficeBroadcaster
{
    protected static function broadcaster(): BaseWsBroadcaster
    {
        return resolve(BackOfficeWsBroadcaster::class);
    }
}
