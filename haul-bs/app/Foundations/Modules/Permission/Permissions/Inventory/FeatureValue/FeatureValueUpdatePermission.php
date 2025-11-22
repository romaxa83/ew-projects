<?php

namespace App\Foundations\Modules\Permission\Permissions\Inventory\FeatureValue;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class FeatureValueUpdatePermission extends BasePermission
{
    public const KEY = FeatureValuePermissionsGroup::KEY . '.update';
}
