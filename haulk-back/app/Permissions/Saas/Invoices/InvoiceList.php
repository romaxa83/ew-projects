<?php


namespace App\Permissions\Saas\Invoices;


use App\Permissions\BasePermission;

class InvoiceList extends BasePermission
{
    public const KEY = 'invoice.list';

    public function getName(): string
    {
        return __('permissions.invoice.grants.list');
    }

    public function getPosition(): int
    {
        return 10;
    }
}
