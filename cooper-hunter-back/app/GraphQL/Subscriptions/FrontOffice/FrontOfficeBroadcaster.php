<?php

namespace App\GraphQL\Subscriptions\FrontOffice;

use App\WebSocket\Broadcasts\FrontOfficeWsBroadcaster;
use Core\WebSocket\Broadcasts\BaseWsBroadcaster;

trait FrontOfficeBroadcaster
{
    protected static function broadcaster(): BaseWsBroadcaster
    {
        return resolve(FrontOfficeWsBroadcaster::class);
    }
}
