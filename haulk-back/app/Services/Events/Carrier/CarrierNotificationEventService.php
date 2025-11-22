<?php


namespace App\Services\Events\Carrier;


use App\Broadcasting\Events\Carrier\UpdateCarrierNotificationBroadcast;
use App\Events\ModelChanged;
use App\Models\Saas\Company\CompanyNotificationSettings;
use App\Services\Events\EventService;

class CarrierNotificationEventService extends EventService
{

    private const HISTORY_MESSAGE_NOTIFICATION_UPDATE = 'history.notification_settings_updated';

    private CompanyNotificationSettings $notification;

    public function __construct(CompanyNotificationSettings $notification)
    {
        $this->notification = $notification;
    }

    private function getHistoryMessage(): ?string
    {
        switch ($this->action) {
            case self::ACTION_UPDATE:
                return self::HISTORY_MESSAGE_NOTIFICATION_UPDATE;
        }
        return null;
    }

    private function getHistoryMeta(): array
    {
        $meta = [
            'role' => $this->user->getRoleName(),
            'full_name' => $this->user->full_name,
            'email' => $this->user->email,
            'user_id' => $this->user->id,
        ];

        return $meta;
    }

    private function setHistory(): void
    {
        event(
            new ModelChanged(
                $this->notification,
                $this->getHistoryMessage(),
                $this->getHistoryMeta(),
            )
        );
    }

    public function update(?string $prefix = null): CarrierNotificationEventService
    {
        $this->action = self::ACTION_UPDATE . ($prefix !== null ? $prefix : '');

        $this->setHistory();

        return $this;
    }

    public function broadcast(): CarrierNotificationEventService
    {
        event(new UpdateCarrierNotificationBroadcast($this->notification->company->id));

        return $this;
    }
}
