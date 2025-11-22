<?php

namespace App\Foundations\Modules\Permission\Permissions\Order\Parts;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class OrderAddCommentPermission extends BasePermission
{
    public const KEY = OrderPermissionsGroup::KEY . '.add-comment';
}
