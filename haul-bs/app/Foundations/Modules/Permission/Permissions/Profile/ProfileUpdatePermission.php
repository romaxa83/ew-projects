<?php

namespace App\Foundations\Modules\Permission\Permissions\Profile;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class ProfileUpdatePermission extends BasePermission
{
    public const KEY = ProfilePermissionsGroup::KEY.'.update';
}
