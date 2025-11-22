<?php

namespace App\Foundations\Modules\Permission\Permissions\Inventory\FeatureValue;

use App\Foundations\Modules\Permission\Permissions\BasePermission;

final readonly class FeatureValueReadPermission extends BasePermission
{
    public const KEY = FeatureValuePermissionsGroup::KEY . '.read';
}
