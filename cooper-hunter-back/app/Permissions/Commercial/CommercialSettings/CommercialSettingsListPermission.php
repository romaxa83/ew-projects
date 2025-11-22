<?php

namespace App\Permissions\Commercial\CommercialSettings;

use Core\Permissions\BasePermission;

class CommercialSettingsListPermission extends BasePermission
{
    public const KEY = CommercialSettingsPermissionGroup::KEY . '.list';

    public function getName(): string
    {
        return __('permissions.commercial_settings.grants.list');
    }

    public function getPosition(): int
    {
        return 0;
    }
}