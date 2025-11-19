<?php

namespace Wezom\Users\GraphQL\Mutations\Site;

use Exception;
use Wezom\Core\GraphQL\BaseFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Users\Services\Site\UserVerificationService;

class SiteUserVerifyEmail extends BaseFieldResolver
{
    public function __construct(private readonly UserVerificationService $userVerificationService)
    {
    }

    /**
     * @throws Exception
     */
    public function resolve(Context $context): bool
    {
        $user = $this->userVerificationService->verifyToken($context->getArg('token'));

        $this->userVerificationService->checkEmailVerified($user);
        $this->userVerificationService->setEmailAsVerified($user);

        return true;
    }
}
