<?php

namespace App\Services\Permissions\Groups\BodyShop;

use App\Services\Permissions\Groups\PermissionGroupAbstract;

class PermissionGroupOrders extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'orders-bs';
    }

    public function getPermissions(): array
    {
        return [
            'create',
            'read',
            'update',
            'delete',
            'add-comment',
            'delete-comment',
            'change-status',
            'reassign-mechanic',
            'generate-invoice',
            'restore',
            'delete-permanently',
            'send-documents',
            'create-payment',
            'delete-payment',
        ];
    }
}
