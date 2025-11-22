<?php

namespace App\Foundations\Modules\Permission\Permissions\Setting;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class SettingReadPermission extends BasePermission
{
    public const KEY = SettingPermissionsGroup::KEY . '.read';
}
