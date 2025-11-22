<?php

namespace App\Foundations\Modules\Permission\Permissions\Inventory\Feature;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class FeatureUpdatePermission extends BasePermission
{
    public const KEY = FeaturePermissionsGroup::KEY . '.update';
}
