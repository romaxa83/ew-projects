<?php

namespace App\Permissions\Warranty\WarrantyRegistration;

use Core\Permissions\BasePermission;

class WarrantyRegistrationDeletePermission extends BasePermission
{
    public const KEY = 'warranty_registration.delete';

    public function getName(): string
    {
        return __('permissions.warranty_registration.grants.delete');
    }

    public function getPosition(): int
    {
        return 4;
    }
}
