<?php


namespace App\Permissions\Saas\Invoices;


use App\Permissions\BasePermissionGroup;

class InvoicePermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'invoice';

    public function getName(): string
    {
        return __('permissions.invoice.group');
    }
}
