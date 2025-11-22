<?php

namespace App\Foundations\Modules\Permission\Permissions\Tag;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class TagReadPermission extends BasePermission
{
    public const KEY = TagPermissionsGroup::KEY . '.read';
}
