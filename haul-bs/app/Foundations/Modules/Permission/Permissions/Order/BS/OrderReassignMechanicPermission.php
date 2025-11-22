<?php

namespace App\Foundations\Modules\Permission\Permissions\Order\BS;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class OrderReassignMechanicPermission extends BasePermission
{
    public const KEY = OrderPermissionsGroup::KEY . '.reassign-mechanic';
}
