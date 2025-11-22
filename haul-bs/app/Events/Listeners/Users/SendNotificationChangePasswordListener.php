<?php

namespace App\Events\Listeners\Users;

use App\Events\Events\Users\UserChangedEvent;
use App\Foundations\Enums\LogKeyEnum;
use App\Services\Users\UserNotificationService;

class SendNotificationChangePasswordListener
{
    public function __construct(protected UserNotificationService $userNotificationService)
    {}

    public function handle(UserChangedEvent $event): void
    {
        try {
            $this->userNotificationService->changePassword($event->getModel());

            logger_info(LogKeyEnum::SendEmail->value."Change password, to [{$event->getModel()->email->getValue()}] SUCCESS");
        } catch (\Throwable $e) {
            logger_info( LogKeyEnum::SendEmail->value."Change password FAILED -" . __CLASS__, [
                'message' => $e->getMessage()
            ]);
        }
    }
}
