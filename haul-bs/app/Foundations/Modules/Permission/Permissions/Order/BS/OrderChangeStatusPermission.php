<?php

namespace App\Foundations\Modules\Permission\Permissions\Order\BS;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class OrderChangeStatusPermission extends BasePermission
{
    public const KEY = OrderPermissionsGroup::KEY . '.change-status';
}
