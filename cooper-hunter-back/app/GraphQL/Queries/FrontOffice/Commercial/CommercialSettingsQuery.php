<?php

namespace App\GraphQL\Queries\FrontOffice\Commercial;

use App\GraphQL\Queries\Common\Commercial\BaseCommercialSettingsQuery;

class CommercialSettingsQuery extends BaseCommercialSettingsQuery
{
    protected function setQueryGuard(): void
    {
        $this->setTechnicianGuard();
    }
}
