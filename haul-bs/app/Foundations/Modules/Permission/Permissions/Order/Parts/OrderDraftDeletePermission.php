<?php

namespace App\Foundations\Modules\Permission\Permissions\Order\Parts;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class OrderDraftDeletePermission extends BasePermission
{
    public const KEY = OrderPermissionsGroup::KEY . '.delete-draft';
}
