<?php

namespace App\Events\Listeners\Users;

use App\Events\Events\Customers\CreateCustomerTaxExemptionEComEvent;
use App\Foundations\Enums\LogKeyEnum;
use App\Foundations\Modules\Permission\Models\Role;
use App\Models\Users\User;
use App\Services\Users\UserNotificationService;
use Illuminate\Database\Eloquent\Builder;

class SendNotificationTaxExemptionListener
{
    public function __construct(protected UserNotificationService $userNotificationService)
    {}

    public function handle(CreateCustomerTaxExemptionEComEvent $event): void
    {
        try {
            $email = config('mail.email_from_tax_exemption');
            $this->userNotificationService->createdTaxExemption($email, $event->getModel());

            logger_info(LogKeyEnum::SendEmail->value."Created tax exemption, to [{$email}] SUCCESS");
        } catch (\Throwable $e) {
            logger_info( LogKeyEnum::SendEmail->value."Created tax exemption FAILED -" . __CLASS__, [
                'message' => $e->getMessage()
            ]);
        }
    }
}
