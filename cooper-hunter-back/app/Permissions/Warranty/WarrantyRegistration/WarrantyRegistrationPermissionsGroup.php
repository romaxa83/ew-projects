<?php

namespace App\Permissions\Warranty\WarrantyRegistration;

use Core\Permissions\BasePermissionGroup;

class WarrantyRegistrationPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'warranty_registration';

    public function getName(): string
    {
        return __('permissions.warranty_registration.group');
    }

    public function getPosition(): int
    {
        return 72;
    }
}
