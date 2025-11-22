<?php

namespace App\Permissions\Companies\ShippingAddress;

use Core\Permissions\BasePermission;

class CompanyShippingAddressListPermission extends BasePermission
{
    public const KEY = 'company.shipping_address.list';

    public function getName(): string
    {
        return __('permissions.company.shipping_address.grants.list');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
