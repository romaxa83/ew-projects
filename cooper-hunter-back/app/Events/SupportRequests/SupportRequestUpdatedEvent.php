<?php


namespace App\Events\SupportRequests;


use App\Contracts\Alerts\AlertEvent;
use App\Contracts\Alerts\AlertModel;
use App\Contracts\Alerts\MetaDataDto;
use App\Contracts\Roles\HasGuardUser;
use App\Contracts\Subscriptions\SupportRequestSubscriptionEvent;
use App\Enums\SupportRequests\SupportRequestSubscriptionActionEnum;
use App\Models\Admins\Admin;
use App\Models\Support\SupportRequest;
use App\Models\Technicians\Technician;

class SupportRequestUpdatedEvent implements AlertEvent, SupportRequestSubscriptionEvent
{
    public function __construct(private SupportRequest $supportRequest)
    {
    }

    public function getInitiator(): ?HasGuardUser
    {
        return null;
    }

    public function getSupportRequest(): SupportRequest
    {
        return $this->supportRequest;
    }

    public function getSender(): Technician|Admin
    {
        return $this->supportRequest->technician;
    }

    public function getAction(): ?string
    {
        return $this->supportRequest->wasChanged(
            'is_closed'
        ) && $this->supportRequest->is_closed === true ? SupportRequestSubscriptionActionEnum::CLOSED : null;
    }

    public function getModel(): AlertModel
    {
        return $this->supportRequest;
    }

    public function isAlertEvent(): bool
    {
        return $this->supportRequest->wasChanged('is_closed') && $this->supportRequest->is_closed === true;
    }

    public function getMetaData(): ?MetaDataDto
    {
        return null;
    }
}
