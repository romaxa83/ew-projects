<?php

namespace App\Services\Auth;

class UserPassportService extends AuthPassportService
{
    public function getClientId(): int
    {
        return config('auth.oauth_client.users.id');
    }

    public function getClientSecret(): string
    {
        return config('auth.oauth_client.users.secret');
    }
}
