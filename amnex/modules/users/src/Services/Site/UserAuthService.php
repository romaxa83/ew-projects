<?php

namespace Wezom\Users\Services\Site;

use Wezom\Core\Entities\Auth\SanctumSession;
use Wezom\Core\Models\Auth\GuestSession;
use Wezom\Core\Services\SanctumAuthService;
use Wezom\Users\Events\Auth\UserLoggedInEvent;
use Wezom\Users\Models\User;

class UserAuthService
{
    protected SanctumAuthService $sanctumService;

    public function __construct(SanctumAuthService $authService)
    {
        $this->sanctumService = $authService;
    }

    public function auth(
        User $user,
        ?GuestSession $guestSession = null
    ): SanctumSession {
        $session = $this->issueToken($user, 'site_app');

        event(new UserLoggedInEvent($user, $guestSession));

        return $session;
    }

    public function issueToken(User $user, string $tokenName): SanctumSession
    {
        return $this->sanctumService->issueToken(
            $user,
            $tokenName,
            $this->getAccessTokenLifetime(),
            $this->getRefreshTokenLifetime()
        );
    }

    public function refreshToken(string $refreshToken): SanctumSession
    {
        return $this->sanctumService->refreshToken(
            $refreshToken,
            $this->getAccessTokenLifetime(),
            $this->getRefreshTokenLifetime()
        );
    }

    public function logout(User $user): void
    {
        $this->sanctumService->logout($user);
    }

    public function logoutAllSessions(User $user): void
    {
        $this->sanctumService->logoutAllSessions($user);
    }

    public function getAccessTokenLifetime(): int
    {
        return config('auth.user_access_token_lifetime');
    }

    public function getRefreshTokenLifetime(): int
    {
        return config('auth.user_refresh_token_lifetime');
    }
}
