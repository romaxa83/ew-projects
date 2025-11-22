<?php

namespace App\WebSocket\Handlers;

use App\Models\Admins\Admin;
use App\WebSocket\Connections\BackOfficeConnectionStorage;
use Core\WebSocket\Handlers\BaseGraphQLWsHandler;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;

class BackOfficeWsHandler extends BaseGraphQLWsHandler
{
    protected function setSchema(): void
    {
        $this->schema = config('graphql.schemas.BackOffice');
    }

    protected function setConnectionStorage(): void
    {
        $this->connectionStorage = resolve(BackOfficeConnectionStorage::class);
    }

    protected function getGuard(): Guard
    {
        return Auth::guard(Admin::GUARD);
    }
}
