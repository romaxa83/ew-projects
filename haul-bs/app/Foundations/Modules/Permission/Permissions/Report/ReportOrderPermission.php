<?php

namespace App\Foundations\Modules\Permission\Permissions\Report;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class ReportOrderPermission extends BasePermission
{
    public const KEY = ReportPermissionsGroup::KEY . '.orders';
}
