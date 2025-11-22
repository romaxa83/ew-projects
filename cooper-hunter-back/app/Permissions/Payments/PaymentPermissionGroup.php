<?php

namespace App\Permissions\Payments;

use Core\Permissions\BasePermissionGroup;

class PaymentPermissionGroup extends BasePermissionGroup
{
    public const KEY = 'payment';

    public function getName(): string
    {
        return __('permissions.payment.group');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
