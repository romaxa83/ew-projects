<?php

namespace Wezom\Admins\GraphQL\Mutations\Back;

use Wezom\Admins\Services\AdminAuthService;
use Wezom\Core\GraphQL\BaseFieldResolver;
use Wezom\Core\GraphQL\Context;

final class BackAdminRefreshToken extends BaseFieldResolver
{
    public const NAME = 'backAdminRefreshToken';

    public function resolve(Context $context): array
    {
        $authService = app(AdminAuthService::class);

        $session = $authService->refreshToken($context->getArg('refresh_token'));

        return [
            'token_type' => 'Bearer',
            'access_token' => $session->getAccessToken()->plainTextToken,
            'access_expires_in' => $authService->getAccessTokenLifetime() * 60,
            'refresh_token' => $session->getRefreshToken()->plainTextToken,
            'refresh_expires_in' => $authService->getRefreshTokenLifetime() * 60,
        ];
    }

    protected function rules(array $args = []): array
    {
        return [
            'refresh_token' => ['required', 'string'],
        ];
    }
}
