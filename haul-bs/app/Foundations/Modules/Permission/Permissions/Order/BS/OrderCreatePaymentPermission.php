<?php

namespace App\Foundations\Modules\Permission\Permissions\Order\BS;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class OrderCreatePaymentPermission extends BasePermission
{
    public const KEY = OrderPermissionsGroup::KEY . '.create-payment';
}
