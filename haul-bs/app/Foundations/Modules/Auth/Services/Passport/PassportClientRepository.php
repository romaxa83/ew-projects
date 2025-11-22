<?php

namespace App\Foundations\Modules\Auth\Services\Passport;

use App\Foundations\Modules\Auth\Models\Passport\Client;
use Illuminate\Database\Eloquent\Builder;

class PassportClientRepository
{
    public function findForAdmin(): ?Client
    {
        return $this->findFor('admins');
    }

    public function findForUser(): ?Client
    {
        return $this->findFor('users');
    }

    public function findFor(string $provider): ?Client
    {
        return $this->query()
            ->where('provider', $provider)
            ->where('password_client', 1)
            ->where('revoked', 0)
            ->first();
    }

    public function query(): Builder|Client
    {
        return Client::query();
    }
}
