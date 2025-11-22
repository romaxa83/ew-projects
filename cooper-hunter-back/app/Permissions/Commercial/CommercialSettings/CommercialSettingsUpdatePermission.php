<?php

namespace App\Permissions\Commercial\CommercialSettings;

use Core\Permissions\BasePermission;

class CommercialSettingsUpdatePermission extends BasePermission
{
    public const KEY = CommercialSettingsPermissionGroup::KEY . '.update';

    public function getName(): string
    {
        return __('permissions.commercial_settings.grants.update');
    }

    public function getPosition(): int
    {
        return 2;
    }
}