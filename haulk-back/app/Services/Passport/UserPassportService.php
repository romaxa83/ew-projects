<?php

namespace App\Services\Passport;

use App\Models\Users\User;
use Illuminate\Contracts\Auth\Authenticatable;

class UserPassportService extends AuthPassportService
{

    public function getClientSecret(): string
    {
        return config('auth.oauth_client.users.secret');
    }

    /**
     * @param Authenticatable|User $authenticatable
     * @return bool
     */
    public function logout(Authenticatable $authenticatable): bool
    {
        $authenticatable->fcm_token = null;
        $authenticatable->save();

        return parent::logout($authenticatable);
    }

    public function getClientId(): int
    {
        return config('auth.oauth_client.users.id');
    }
}
