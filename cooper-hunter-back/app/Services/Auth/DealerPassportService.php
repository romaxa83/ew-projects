<?php

namespace App\Services\Auth;

use Core\Services\Auth\AuthPassportService;

class DealerPassportService extends AuthPassportService
{
    public function getClientId(): int
    {
        return config('auth.oauth_client.dealers.id');
    }

    public function getClientSecret(): string
    {
        return config('auth.oauth_client.dealers.secret');
    }
}
