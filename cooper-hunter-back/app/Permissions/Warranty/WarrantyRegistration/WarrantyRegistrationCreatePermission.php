<?php

namespace App\Permissions\Warranty\WarrantyRegistration;

use Core\Permissions\BasePermission;

class WarrantyRegistrationCreatePermission extends BasePermission
{
    public const KEY = 'warranty_registration.create';

    public function getName(): string
    {
        return __('permissions.warranty_registration.grants.create');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
