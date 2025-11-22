<?php

namespace App\WebSocket\Services;

use App\Models\Admins\Admin;
use Core\WebSocket\Services\WsAuthService;

class BackOfficeWsAuthService extends WsAuthService
{
    public const GUARD = Admin::GUARD;
}
