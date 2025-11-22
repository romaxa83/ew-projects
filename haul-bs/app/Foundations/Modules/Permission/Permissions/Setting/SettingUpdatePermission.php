<?php

namespace App\Foundations\Modules\Permission\Permissions\Setting;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class SettingUpdatePermission extends BasePermission
{
    public const KEY = SettingPermissionsGroup::KEY . '.update';
}
