<?php

namespace App\Permissions\Companies\ShippingAddress;

use Core\Permissions\BasePermission;

class CompanyShippingAddressUpdatePermission extends BasePermission
{
    public const KEY = 'company.shipping_address.update';

    public function getName(): string
    {
        return __('permissions.company.shipping_address.grants.update');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
