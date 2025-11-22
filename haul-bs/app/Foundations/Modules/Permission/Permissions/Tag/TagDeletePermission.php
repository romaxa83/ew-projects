<?php

namespace App\Foundations\Modules\Permission\Permissions\Tag;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class TagDeletePermission extends BasePermission
{
    public const KEY = TagPermissionsGroup::KEY . '.delete';
}
