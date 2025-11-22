<?php

namespace App\WebSocket\Handlers;

use App\Models\Users\User;
use App\WebSocket\Connections\FrontOfficeConnectionStorage;
use Core\WebSocket\Handlers\BaseGraphQLWsHandler;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;

class FrontOfficeWsHandler extends BaseGraphQLWsHandler
{
    protected function setSchema(): void
    {
        $this->schema = config('graphql.schemas.default');
    }

    protected function setConnectionStorage(): void
    {
        $this->connectionStorage = resolve(FrontOfficeConnectionStorage::class);
    }

    protected function getGuard(): Guard
    {
        return Auth::guard(User::GUARD);
    }
}
