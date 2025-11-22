<?php

namespace App\Permissions\Companies\ShippingAddress;

use Core\Permissions\BasePermission;

class CompanyShippingAddressDeletePermission extends BasePermission
{
    public const KEY = 'company.shipping_address.delete';

    public function getName(): string
    {
        return __('permissions.company.shipping_address.grants.delete');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
