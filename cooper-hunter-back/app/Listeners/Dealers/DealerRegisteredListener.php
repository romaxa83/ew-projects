<?php

namespace App\Listeners\Dealers;

use App\Events\Dealers\DealerRegisteredEvent;
use App\Services\Dealers\DealerVerificationService;

class DealerRegisteredListener
{
    public function __construct(
        protected DealerVerificationService $verificationService
    )
    {}

    public function handle(DealerRegisteredEvent $event): void
    {
        $user = $event->getDealer();

        if (!$user->isEmailVerified()) {
            $this->verificationService->verifyEmail($user);
        }
    }
}

