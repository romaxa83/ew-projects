<?php

namespace Wezom\Users\GraphQL\Mutations\Site;

use Wezom\Core\GraphQL\BaseFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Users\Models\User;
use Wezom\Users\Services\Site\UserAuthService;

final class SiteUserLogout extends BaseFieldResolver
{
    public function __construct(private readonly UserAuthService $service)
    {
    }

    public function resolve(Context $context): bool
    {
        /** @var User $user */
        $user = $context->getUser();

        $this->service->logout($user);

        return true;
    }

    protected function guards(): string
    {
        return User::GUARD;
    }
}
