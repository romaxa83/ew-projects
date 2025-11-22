<?php

namespace App\GraphQL\Queries\BackOffice\Commercial;

use App\GraphQL\Queries\Common\Commercial\BaseCommercialSettingsQuery;

class CommercialSettingsQuery extends BaseCommercialSettingsQuery
{
    protected function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }
}
