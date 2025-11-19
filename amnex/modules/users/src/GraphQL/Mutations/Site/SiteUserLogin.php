<?php

namespace Wezom\Users\GraphQL\Mutations\Site;

use Wezom\Core\GraphQL\BaseFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Core\Mappers\SanctumSessionMapper;
use Wezom\Core\Models\Auth\GuestSession;
use Wezom\Users\Models\User;
use Wezom\Users\Rules\LoginUserRule;
use Wezom\Users\Services\Site\UserAuthService;

final class SiteUserLogin extends BaseFieldResolver
{
    public const NAME = 'siteUserLogin';

    public function __construct(
        private readonly SanctumSessionMapper $sanctumSessionMapper,
        private readonly UserAuthService $userAuthService,
    ) {
    }

    public function resolve(Context $context): array
    {
        $sessionArg = $context->getArg('session');
        $guestSession = ! empty($sessionArg)
            ? GuestSession::findBySession($sessionArg)
            : null;

        $user = User::findByEmailOrFail($context->getArg('email'));

        $session = $this->userAuthService->auth($user, $guestSession);

        return $this->sanctumSessionMapper->mapToType($session);
    }

    public function rules(array $args = []): array
    {
        return [
            'email' => ['required', 'string', 'email:filter'],
            'password' => ['required', 'string', 'min:' . config('users.min-password-length'), new LoginUserRule($args)],
            'session' => ['nullable', 'string'],
        ];
    }
}
