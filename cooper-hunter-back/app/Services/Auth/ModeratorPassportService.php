<?php

namespace App\Services\Auth;

use Core\Services\Auth\AuthPassportService;

class ModeratorPassportService extends AuthPassportService
{
    public function getClientId(): int
    {
        return config('auth.oauth_client.1c_moderators.id');
    }

    public function getClientSecret(): string
    {
        return config('auth.oauth_client.1c_moderators.secret');
    }
}
