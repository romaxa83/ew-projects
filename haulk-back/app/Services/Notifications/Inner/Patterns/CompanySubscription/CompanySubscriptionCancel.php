<?php

namespace App\Services\Notifications\Inner\Patterns\CompanySubscription;

use App\Enums\Notifications\NotificationAction;
use App\Enums\Notifications\NotificationPlace;
use App\Enums\Notifications\NotificationType;
use App\Models\Saas\Company\Company;
use App\Services\Notifications\Inner\BaseNotificationService;

class CompanySubscriptionCancel extends BaseNotificationService
{
    private Company $model;

    public function __construct(Company $model)
    {
        $this->model = $model;
    }

    public function data(): array
    {
        return [
            'type' => NotificationType::COMPANY_SUBSCRIPTION(),
            'place' => NotificationPlace::BACKOFFICE(),
            'message_key' => 'notification.company_subscription.cancel',
            'message_attr' => [
                'company_name' => $this->model->name,
            ],
            'meta' => [
                'company_id' => $this->model->id,
                'company_name' => $this->model->name,
            ],
            'action' => NotificationAction::TO_DEVICE_CANCEL_SUBSCRIPTION
        ];
    }
}
