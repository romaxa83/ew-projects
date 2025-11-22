<?php

namespace App\Services\Auth;

use Core\Services\Auth\AuthPassportService;

class TechnicianPassportService extends AuthPassportService
{
    public function getClientId(): int
    {
        return config('auth.oauth_client.technicians.id');
    }

    public function getClientSecret(): string
    {
        return config('auth.oauth_client.technicians.secret');
    }
}
