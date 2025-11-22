<?php

namespace App\Listeners\Technicians;

use App\Events\Technicians\TechnicianRegisteredEvent;
use App\Exceptions\Auth\TokenEncryptException;
use App\Services\Technicians\TechnicianVerificationService;

class TechnicianRegisteredListener
{
    public function __construct(private TechnicianVerificationService $technicianVerificationService)
    {
    }

    /**
     * @param TechnicianRegisteredEvent $event
     * @throws TokenEncryptException
     */
    public function handle(TechnicianRegisteredEvent $event): void
    {
        $user = $event->getTechnician();

        if (!$user->isEmailVerified()) {
            $this->technicianVerificationService->verifyEmail($user);
        }
    }
}
