<?php

namespace App\Events\Technicians;

use App\Contracts\Alerts\AlertEvent;
use App\Contracts\Alerts\AlertModel;
use App\Contracts\Alerts\MetaDataDto;
use App\Contracts\Members\Member;
use App\Contracts\Roles\HasGuardUser;
use App\Contracts\Subscriptions\MemberSubscriptionEvent;
use App\Dto\Alerts\MetaData\TechnicianDto;
use App\Models\Technicians\Technician;

class TechnicianUpdatedEvent implements AlertEvent, MemberSubscriptionEvent
{
    private ?array $metaData = null;

    public function __construct(private Technician $technician)
    {}

    public function getTechnician(): Technician
    {
        return $this->technician;
    }

    public function getInitiator(): ?HasGuardUser
    {
        return null;
    }

    public function getModel(): AlertModel
    {
        return $this->technician;
    }

    public function isAlertEvent(): bool
    {
        if ($this->technician->wasChanged('is_verified')) {
            $this->metaData['moderation'] = true;

            return true;
        }

        if ($this->technician->wasChanged('email_verified_at')) {
            $this->metaData['email_verification'] = true;

            return true;
        }

        return false;
    }

    public function getMetaData(): ?MetaDataDto
    {
        return TechnicianDto::fromEvent($this->metaData);
    }

    public function getMember(): Member
    {
        return $this->technician;
    }
}
