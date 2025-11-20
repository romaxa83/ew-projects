<?php

namespace App\Services\Auth;

use Core\Services\Auth\AuthPassportService;

class EmployeePassportService extends AuthPassportService
{
    public function getClientId(): int
    {
        return config('auth.oauth_client.employees.id');
    }

    public function getClientSecret(): string
    {
        return config('auth.oauth_client.employees.secret');
    }
}
