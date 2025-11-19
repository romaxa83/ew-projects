<?php

namespace Wezom\Users\GraphQL\Mutations\Site;

use Exception;
use Wezom\Core\GraphQL\BaseFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Core\Mappers\SanctumSessionMapper;
use Wezom\Core\Models\Auth\GuestSession;
use Wezom\Users\Dto\UserRegistrationDto;
use Wezom\Users\Services\Site\UserAuthService;
use Wezom\Users\Services\Site\UserRegistrationService;
use Wezom\Users\Services\Site\UserVerificationService;

class SiteUserRegistration extends BaseFieldResolver
{
    protected bool $runInTransaction = true;
    protected array $dtoRulesMap = [
        'user' => UserRegistrationDto::class,
    ];

    public function __construct(
        private readonly SanctumSessionMapper $sanctumSessionMapper,
        private readonly UserAuthService $userAuthService,
        private readonly UserRegistrationService $userRegistrationService,
        private readonly UserVerificationService $userVerificationService,
    ) {
    }

    /**
     * @throws Exception
     */
    public function resolve(Context $context): array
    {
        $dto = $context->getDto(UserRegistrationDto::class, 'user');

        $user = $this->userRegistrationService->register($dto);

        $this->userVerificationService->initEmailVerification($user);

        $sessionArg = $context->getArg('session');
        $guestSession = ! empty($sessionArg)
            ? GuestSession::findBySession($sessionArg)
            : null;

        $session = $this->userAuthService->auth($user, $guestSession);

        return $this->sanctumSessionMapper->mapToType($session);
    }
}
