<?php

declare(strict_types=1);

namespace Wezom\Admins\Services;

use Wezom\Admins\Models\Admin;
use Wezom\Core\Entities\Auth\SanctumSession;
use Wezom\Core\Services\SanctumAuthService;

class AdminAuthService
{
    protected SanctumAuthService $sanctumService;

    public function __construct(SanctumAuthService $authService)
    {
        $this->sanctumService = $authService;
    }

    public function auth(Admin $admin): SanctumSession
    {
        if (config('auth.admin_revoke_all_token')) {
            app(AdminVerificationService::class)->revokeToken($admin);
        }

        return $this->sanctumService->issueToken(
            $admin,
            'admin_panel_app',
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

    public function logout(Admin $admin): void
    {
        $this->sanctumService->logout($admin);
    }

    public function getAccessTokenLifetime(): int
    {
        return config('auth.admin_access_token_lifetime');
    }

    public function getRefreshTokenLifetime(): int
    {
        return config('auth.admin_refresh_token_lifetime');
    }
}
