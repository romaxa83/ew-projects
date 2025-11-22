<?php

namespace App\Foundations\Modules\Permission\Permissions\Tag;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class TagCreatePermission extends BasePermission
{
    public const KEY = TagPermissionsGroup::KEY . '.create';
}
