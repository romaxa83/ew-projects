<?php

namespace App\Permissions\Warranty\WarrantyRegistration;

use Core\Permissions\BasePermission;

class WarrantyRegistrationUpdatePermission extends BasePermission
{
    public const KEY = 'warranty_registration.update';

    public function getName(): string
    {
        return __('permissions.warranty_registration.grants.update');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
