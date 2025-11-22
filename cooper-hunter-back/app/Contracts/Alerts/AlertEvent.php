<?php


namespace App\Contracts\Alerts;


use App\Contracts\Roles\HasGuardUser;

interface AlertEvent
{
    public function getInitiator(): ?HasGuardUser;

    public function getModel(): AlertModel;

    public function isAlertEvent(): bool;

    public function getMetaData(): ?MetaDataDto;
}
