<?php

namespace Wezom\Admins\GraphQL\Mutations\Back;

use Wezom\Admins\Models\Admin;
use Wezom\Admins\Services\AdminAuthService;
use Wezom\Core\GraphQL\BaseFieldResolver;
use Wezom\Core\GraphQL\Context;

final class BackAdminLogin extends BaseFieldResolver
{
    public const NAME = 'backAdminLogin';

    public function resolve(Context $context): array
    {
        $authService = app(AdminAuthService::class);

        $admin = Admin::query()->where('email', $context->getArg('email'))->first();

        $session = $authService->auth($admin);

        return [
            'token_type' => 'Bearer',
            'access_token' => $session->getAccessToken()->plainTextToken,
            'access_expires_in' => $authService->getAccessTokenLifetime() * 60,
            'refresh_token' => $session->getRefreshToken()->plainTextToken,
            'refresh_expires_in' => $authService->getRefreshTokenLifetime() * 60,
        ];
    }
}
