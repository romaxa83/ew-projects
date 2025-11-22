<?php

namespace App\Foundations\Modules\Permission\Permissions\Order\BS;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class OrderDeletePaymentPermission extends BasePermission
{
    public const KEY = OrderPermissionsGroup::KEY . '.delete-payment';
}
