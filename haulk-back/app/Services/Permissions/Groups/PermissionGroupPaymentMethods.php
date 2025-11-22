<?php

namespace App\Services\Permissions\Groups;

class PermissionGroupPaymentMethods extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'payment-methods';
    }

    public function getPermissions(): array
    {
        return [
            'create',
            'read',
            'update',
            'delete',
        ];
    }
}
