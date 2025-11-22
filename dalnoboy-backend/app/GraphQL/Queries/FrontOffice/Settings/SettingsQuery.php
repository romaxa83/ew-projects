<?php

namespace App\GraphQL\Queries\FrontOffice\Settings;

use App\GraphQL\Queries\Common\Settings\BaseSettingsQuery;

class SettingsQuery extends BaseSettingsQuery
{
    protected function setQueryGuard(): void
    {
        $this->setUserGuard();
    }
}
