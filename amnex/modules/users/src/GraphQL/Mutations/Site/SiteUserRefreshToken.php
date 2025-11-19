<?php

namespace Wezom\Users\GraphQL\Mutations\Site;

use Wezom\Core\GraphQL\BaseFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Core\Mappers\SanctumSessionMapper;
use Wezom\Users\Services\Site\UserAuthService;

class SiteUserRefreshToken extends BaseFieldResolver
{
    public function __construct(
        private readonly SanctumSessionMapper $sanctumSessionMapper,
        private readonly UserAuthService $userAuthService,
    ) {
    }

    public function resolve(Context $context): array
    {
        $session = $this->userAuthService->refreshToken($context->getArg('refreshToken'));

        return $this->sanctumSessionMapper->mapToType($session);
    }
}
