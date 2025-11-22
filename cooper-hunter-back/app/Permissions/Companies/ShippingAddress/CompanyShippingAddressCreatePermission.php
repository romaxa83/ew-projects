<?php

namespace App\Permissions\Companies\ShippingAddress;

use Core\Permissions\BasePermission;

class CompanyShippingAddressCreatePermission extends BasePermission
{
    public const KEY = 'company.shipping_address.create';

    public function getName(): string
    {
        return __('permissions.company.shipping_address.grants.create');
    }

    public function getPosition(): int
    {
        return 3;
    }
}



