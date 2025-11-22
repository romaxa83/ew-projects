<?php


namespace App\Events\SupportRequests;


use App\Contracts\Subscriptions\SupportRequestSubscriptionEvent;
use App\Enums\SupportRequests\SupportRequestSubscriptionActionEnum;
use App\Models\Admins\Admin;
use App\Models\Support\SupportRequest;
use App\Models\Technicians\Technician;

class SupportRequestCreatedEvent implements SupportRequestSubscriptionEvent
{
    public function __construct(private SupportRequest $supportRequest)
    {
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
        return SupportRequestSubscriptionActionEnum::CREATED;
    }
}
