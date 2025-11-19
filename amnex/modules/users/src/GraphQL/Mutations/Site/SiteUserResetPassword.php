<?php

namespace Wezom\Users\GraphQL\Mutations\Site;

use Wezom\Core\GraphQL\BaseFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Users\Rules\PasswordConfirmationRule;
use Wezom\Users\Rules\UserPasswordRule;
use Wezom\Users\Services\Site\UserAuthService;
use Wezom\Users\Services\Site\UserPasswordResetService;
use Wezom\Users\Services\Site\UserVerificationService;

class SiteUserResetPassword extends BaseFieldResolver
{
    public function __construct(
        private readonly UserAuthService $userAuthService,
        private readonly UserPasswordResetService $userPasswordResetService,
        private readonly UserVerificationService $userVerificationService,
    ) {
    }

    public function resolve(Context $context): bool
    {
        $user = $this->userPasswordResetService->verifyToken($context->getArg('token'));
        $newPassword = $context->getArg('password');

        $this->userPasswordResetService->changePassword($user, $newPassword);
        $this->userVerificationService->setEmailAsVerified($user);
        $this->userAuthService->logoutAllSessions($user);

        return true;
    }

    public function rules(array $args = []): array
    {
        return [
            'token' => ['required', 'string'],
            'password' => ['required', 'string', new UserPasswordRule()],
            'passwordConfirmation' => ['required', 'string', new PasswordConfirmationRule($args['password'])],
        ];
    }
}
