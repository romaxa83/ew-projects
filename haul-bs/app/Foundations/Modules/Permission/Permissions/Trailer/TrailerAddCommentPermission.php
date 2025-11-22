<?php

namespace App\Foundations\Modules\Permission\Permissions\Trailer;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class TrailerAddCommentPermission extends BasePermission
{
    public const KEY = TrailerPermissionsGroup::KEY . '.add-comment';
}
