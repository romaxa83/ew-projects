<?php

namespace Wezom\Users\GraphQL\Mutations\Site;

use Exception;
use Wezom\Core\GraphQL\BaseFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Users\Models\User;
use Wezom\Users\Services\Site\UserVerificationService;

class SiteUserResendEmailVerification extends BaseFieldResolver
{
    public function __construct(
        private readonly UserVerificationService $userVerificationService
    ) {
    }

    /**
     * @throws Exception
     */
    public function resolve(Context $context): bool
    {
        /** @var User $user */
        $user = $context->getUser();

        $this->userVerificationService->initEmailVerification($user);

        return true;
    }

    protected function guards(): string
    {
        return User::GUARD;
    }
}
