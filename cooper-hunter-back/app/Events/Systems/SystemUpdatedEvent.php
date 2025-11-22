<?php

namespace App\Events\Systems;

use App\Contracts\Alerts\AlertEvent;
use App\Contracts\Alerts\AlertModel;
use App\Contracts\Alerts\MetaDataDto;
use App\Contracts\Roles\HasGuardUser;
use App\Models\Projects\System;

class SystemUpdatedEvent implements AlertEvent
{
    public function __construct(private System $system)
    {
    }

    public function getInitiator(): ?HasGuardUser
    {
        return null;
    }

    public function getModel(): AlertModel
    {
        return $this->system;
    }

    public function isAlertEvent(): bool
    {
        return $this->system->wasChanged('warranty_status');
    }

    public function getMetaData(): ?MetaDataDto
    {
        return null;
    }
}
