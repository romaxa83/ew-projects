<?php

namespace App\Listeners\Users;

use App\Events\Users\UserRegisteredEvent;
use App\Services\Users\UserVerificationService;
use Exception;

class UserRegisteredListener
{
    public function __construct(private UserVerificationService $userVerificationService)
    {
    }

    /**
     * @param UserRegisteredEvent $event
     * @throws Exception
     */
    public function handle(UserRegisteredEvent $event): void
    {
        $user = $event->getUser();

        if (!$user->isEmailVerified()) {
            $this->userVerificationService->verifyEmail($user);
        }
    }
}
