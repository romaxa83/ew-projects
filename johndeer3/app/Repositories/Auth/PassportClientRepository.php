<?php

namespace App\Repositories\Auth;

use Laravel\Passport\Client;

class PassportClientRepository
{
    public function query()
    {
        return Client::query();
    }

    public function find(): null|Client
    {
        return $this->query()
            ->where('password_client', 1)
            ->where('revoked', 0)
            ->first();
    }
}
