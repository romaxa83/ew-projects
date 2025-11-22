<?php

namespace App\Permissions\Companies\ShippingAddress;

use Core\Permissions\BasePermissionGroup;

class CompanyShippingAddressPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'company.shipping_address';

    public function getName(): string
    {
        return __('permissions.company.shipping_address.group');
    }
}


