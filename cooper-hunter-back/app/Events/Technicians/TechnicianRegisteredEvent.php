<?php

namespace App\Events\Technicians;

use App\Contracts\Alerts\AlertEvent;
use App\Contracts\Alerts\AlertModel;
use App\Contracts\Alerts\MetaDataDto;
use App\Contracts\Roles\HasGuardUser;
use App\Dto\Alerts\MetaData\TechnicianDto;
use App\Models\Technicians\Technician;

class TechnicianRegisteredEvent implements AlertEvent
{
    private ?array $metaData = null;

    public function __construct(private Technician $technician)
    {
    }

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
        $this->metaData['registration'] = true;
        return true;
    }

    public function getMetaData(): ?MetaDataDto
    {
        return TechnicianDto::fromEvent($this->metaData);
    }
}
