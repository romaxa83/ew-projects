<?php

namespace App\Foundations\Modules\Permission\Permissions\Profile;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class ProfileReadPermission extends BasePermission
{
    public const KEY = ProfilePermissionsGroup::KEY.'.read';
}
