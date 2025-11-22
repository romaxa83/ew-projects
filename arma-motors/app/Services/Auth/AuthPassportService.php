<?php

namespace App\Services\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Throwable;

abstract class AuthPassportService
{
    public function __construct(
        protected PassportService $passportService
    ) {}

    /**
     * @param string $username
     * @param string $password
     * @return array
     * @throws Throwable
     */
    public function auth(string $username, string $password): array
    {
        return $this->passportService->auth(
            $username,
            $password,
            $this->getClientId(),
            $this->getClientSecret()
        );
    }

    abstract public function getClientId(): int;

    abstract public function getClientSecret(): string;

    /**
     * @param string $refreshToken
     * @return array
     * @throws Throwable
     */
    public function refreshToken(string $refreshToken): array
    {
        return $this->passportService->refreshToken(
            $refreshToken,
            $this->getClientId(),
            $this->getClientSecret()
        );
    }

    public function logout(Authenticatable $authenticatable): bool
    {
        return !!$this->passportService->revokeTokens($authenticatable->getAuthIdentifier(), $this->getClientId());
    }
}
