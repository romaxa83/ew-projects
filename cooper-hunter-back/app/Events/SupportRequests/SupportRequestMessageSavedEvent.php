<?php


namespace App\Events\SupportRequests;


use App\Contracts\Alerts\AlertEvent;
use App\Contracts\Alerts\AlertModel;
use App\Contracts\Roles\HasGuardUser;
use App\Contracts\Subscriptions\SupportRequestSubscriptionEvent;
use App\Dto\Alerts\MetaData\SupportRequestMessageDto;
use App\Enums\SupportRequests\SupportRequestSubscriptionActionEnum;
use App\Models\Admins\Admin;
use App\Models\Support\SupportRequest;
use App\Models\Support\SupportRequestMessage;
use App\Models\Technicians\Technician;

class SupportRequestMessageSavedEvent implements AlertEvent, SupportRequestSubscriptionEvent
{
    public function __construct(private SupportRequestMessage $supportRequestMessage)
    {
    }

    public function getSupportRequest(): SupportRequest
    {
        return $this->supportRequestMessage->supportRequest;
    }

    public function getSender(): Technician|Admin
    {
        return $this->supportRequestMessage->sender;
    }

    public function getAction(): ?string
    {
        return SupportRequestSubscriptionActionEnum::ADDED_MESSAGE;
    }

    public function getInitiator(): ?HasGuardUser
    {
        return $this->supportRequestMessage->sender;
    }

    public function getModel(): AlertModel
    {
        return $this->supportRequestMessage->supportRequest;
    }

    public function isAlertEvent(): bool
    {
        return !$this->supportRequestMessage->supportRequest->wasRecentlyCreated;
    }

    public function getMetaData(): ?SupportRequestMessageDto
    {
        return SupportRequestMessageDto::fromEvent(
            [
                'message' => $this->supportRequestMessage
            ]
        );
    }
}
