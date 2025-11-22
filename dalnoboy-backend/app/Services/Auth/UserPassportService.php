<?php

namespace App\Services\Auth;

use App\Enums\Users\AuthorizationExpirationPeriodEnum;
use App\Models\Users\User;
use Core\Services\Auth\AuthPassportService;
use Core\Traits\Auth\AuthGuardsTrait;
use Laravel\Passport\RefreshToken;

class UserPassportService extends AuthPassportService
{
    use AuthGuardsTrait;

    public function getClientId(): int
    {
        return config('auth.oauth_client.users.id');
    }

    public function getClientSecret(): string
    {
        return config('auth.oauth_client.users.secret');
    }

    public function auth(string $username, string $password): array
    {
        $data = parent::auth($username, $password);

        $tokenData = getRefreshTokenData($data['refresh_token']);
        $user = User::find($tokenData->userId);

        if (!$user || $user->authorization_expiration_period->value === AuthorizationExpirationPeriodEnum::UNLIMITED) {
            return $data;
        }

        $token = RefreshToken::find($tokenData->id);
        $token->expires_at = now()->endOfDay();
        $token->save();

        $data['refresh_token_expires_in'] = $token->expires_at;

        return $data;
    }
}
