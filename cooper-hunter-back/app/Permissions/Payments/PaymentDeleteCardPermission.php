<?php

namespace App\Permissions\Payments;

use Core\Permissions\BasePermission;

class PaymentDeleteCardPermission extends BasePermission
{
    public const KEY = PaymentPermissionGroup::KEY.'.delete-card';

    public function getName(): string
    {
        return __('permissions.payment.grants.card.delete');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
