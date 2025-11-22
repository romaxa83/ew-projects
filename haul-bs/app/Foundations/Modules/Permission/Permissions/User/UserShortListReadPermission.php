<?php

namespace App\Foundations\Modules\Permission\Permissions\User;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class UserShortListReadPermission extends BasePermission
{
    public const KEY = UserPermissionsGroup::KEY . '.read-shortlist';
}
