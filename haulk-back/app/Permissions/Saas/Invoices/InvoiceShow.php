<?php


namespace App\Permissions\Saas\Invoices;


use App\Permissions\BasePermission;

class InvoiceShow extends BasePermission
{
    public const KEY = 'invoice.show';

    public function getName(): string
    {
        return __('permissions.invoice.grants.show');
    }

    public function getPosition(): int
    {
        return 10;
    }
}
