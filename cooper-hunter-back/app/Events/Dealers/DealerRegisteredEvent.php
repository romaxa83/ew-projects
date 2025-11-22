<?php

namespace App\Events\Dealers;

use App\Contracts\Alerts\AlertEvent;
use App\Contracts\Alerts\AlertModel;
use App\Contracts\Alerts\MetaDataDto;
use App\Contracts\Roles\HasGuardUser;
use App\Dto\Alerts\MetaData\DealerDto;
use App\Models\Dealers\Dealer;

class DealerRegisteredEvent implements AlertEvent
{
    private ?array $metaData = null;

    public function __construct(protected Dealer $model)
    {}

    public function getDealer(): Dealer
    {
        return $this->model;
    }

    public function getInitiator(): ?HasGuardUser
    {
        return null;
    }

    public function getModel(): AlertModel
    {
        return $this->model;
    }

    public function isAlertEvent(): bool
    {
        $this->metaData['registration'] = true;
        return true;
    }

    public function getMetaData(): ?MetaDataDto
    {
        return DealerDto::fromEvent($this->metaData);
    }
}
