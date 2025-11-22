<?php

namespace App\Foundations\Modules\Permission\Permissions\Inventory\Feature;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class FeatureDeletePermission extends BasePermission
{
    public const KEY = FeaturePermissionsGroup::KEY . '.delete';
}
