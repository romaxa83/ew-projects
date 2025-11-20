<?php

namespace App\WebSocket\Services;

use App\Models\Users\User;
use Core\WebSocket\Services\WsAuthService;

class FrontOfficeWsAuthService extends WsAuthService
{
    public const GUARD = User::GUARD;
}
