<?php

namespace App\Permissions\Warranty\WarrantyRegistration;

use Core\Permissions\BasePermission;

class WarrantyRegistrationListPermission extends BasePermission
{
    public const KEY = 'warranty_registration.list';

    public function getName(): string
    {
        return __('permissions.warranty_registration.grants.list');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
