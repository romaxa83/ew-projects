<?php

namespace App\Permissions\Payments;

use Core\Permissions\BasePermission;

class PaymentAddCardPermission extends BasePermission
{
    public const KEY = PaymentPermissionGroup::KEY.'.add-card';

    public function getName(): string
    {
        return __('permissions.payment.grants.card.add');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
