<?php

namespace App\Services\Permissions\Groups;

class PermissionGroupOrders extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'orders';
    }

    public function getPermissions(): array
    {
        return [
            'create',
            'read',
            'order-review',
            'update',
            'update-own',
            'delete',
            'delete-own',
            'restore',
            'restore-own',
            'delete-permanently',
            'send-invoice',
            'send-bol',
            'deduct-from-driver',
            'inspection',
            'add-comment',
            'delete-comment',
            'add-attachment',
            'take-offer',
            'release-offer',
            'change-status',
            'send-signature-link',
            'payment-stage-create',
            'payment-stage-delete',
            'export',
        ];
    }
}
