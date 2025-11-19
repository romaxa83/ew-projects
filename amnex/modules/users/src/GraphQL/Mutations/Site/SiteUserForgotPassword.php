<?php

namespace Wezom\Users\GraphQL\Mutations\Site;

use Exception;
use Wezom\Core\GraphQL\BaseFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Users\Models\User;
use Wezom\Users\Services\Site\UserPasswordResetService;

class SiteUserForgotPassword extends BaseFieldResolver
{
    public function __construct(private readonly UserPasswordResetService $service)
    {
    }

    /**
     * @throws Exception
     */
    public function resolve(Context $context): bool
    {
        $user = User::findByEmailOrFail($context->getArg('email'));

        $this->service->initPasswordReset($user);

        return true;
    }
}
